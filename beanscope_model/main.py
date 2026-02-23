"""
BeanScope Coffee Defect Detection API
=====================================
FastAPI application untuk klasifikasi cacat biji kopi menggunakan EfficientNetB0.

Kelas yang dapat dideteksi:
- Broken: Biji kopi pecah/patah
- Fullblack: Biji kopi hitam sempurna
- Fungus Damage: Kerusakan jamur
- Green: Biji kopi masih hijau (belum matang)
- Insect Damage: Kerusakan akibat serangga
"""

import os
import io
import logging
import zipfile
import tempfile
import shutil
from typing import Dict, List, Optional
import numpy as np
import tensorflow as tf
from PIL import Image
from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from tensorflow.keras.applications.efficientnet import preprocess_input

# Konfigurasi logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Inisialisasi FastAPI
app = FastAPI(
    title="BeanScope Coffee Defect Detection API",
    description="API untuk mendeteksi cacat pada biji kopi menggunakan Deep Learning",
    version="1.0.0"
)

# Setup CORS - Allow all origins (sesuaikan untuk production)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ==================== KONFIGURASI ====================
# Menggunakan best_model.keras sebagai model utama
MODEL_PATH = r"D:\Skripsi\bestmodel\BeanScope\beanscope_model\model\bestku_model.keras"

CLASS_NAMES = [
    "Broken",           # Biji pecah
    "Full Black",       # Biji hitam sempurna
    "Fungus Damage",    # Kerusakan jamur
    "Green",            # Biji hijau (belum matang)
    "Insect Damage"     # Kerusakan serangga
]

IMG_SIZE = (224, 224)  # Input size untuk EfficientNetB0
MAX_FILE_SIZE = 10 * 1024 * 1024  # 10MB

# Global model variable
model: Optional[tf.keras.Model] = None


# ==================== FUNGSI UTILITAS ====================

def load_model() -> bool:
    """
    Memuat model dari file .keras.
    
    Returns:
        bool: True jika berhasil memuat model, False jika gagal
    """
    global model
    
    if not os.path.exists(MODEL_PATH):
        logger.error(f"File model tidak ditemukan: {MODEL_PATH}")
        return False
        
    try:
        logger.info(f"Mencoba memuat model dari: {MODEL_PATH}")
        model = tf.keras.models.load_model(
            MODEL_PATH,
            compile=False,      # Tidak perlu compile untuk inference
            safe_mode=False     # Diperlukan untuk model hasil hyperparameter tuning
        )
        logger.info(f"✅ Berhasil memuat model dari: {MODEL_PATH}")
        logger.info(f"📊 Total parameter: {model.count_params():,}")
        return True
        
    except Exception as e:
        logger.error(f"❌ Gagal memuat model dari {MODEL_PATH}: {str(e)}")
        return False


def prepare_image(image_bytes: bytes) -> np.ndarray:
    """
    Preprocessing gambar untuk input ke model.
    
    Args:
        image_bytes: Bytes dari gambar yang diupload
        
    Returns:
        np.ndarray: Array gambar yang sudah dipreprocess dengan shape (1, 224, 224, 3)
        
    Raises:
        ValueError: Jika gambar tidak valid atau tidak bisa diproses
    """
    try:
        # Validasi ukuran file
        if len(image_bytes) > MAX_FILE_SIZE:
            raise ValueError(f"Ukuran file terlalu besar. Maksimal {MAX_FILE_SIZE / 1024 / 1024:.0f}MB")
        
        # Buka gambar dari bytes
        img = Image.open(io.BytesIO(image_bytes))
        
        # Konversi ke RGB jika perlu (handle PNG dengan alpha channel, dll)
        if img.mode != 'RGB':
            img = img.convert('RGB')
        
        # Resize dengan kualitas terbaik (LANCZOS)
        img = img.resize(IMG_SIZE, Image.Resampling.LANCZOS)
        
        # Konversi ke array numpy
        img_array = tf.keras.preprocessing.image.img_to_array(img)
        
        # Tambahkan batch dimension: (224, 224, 3) -> (1, 224, 224, 3)
        img_array = np.expand_dims(img_array, axis=0)
        
        # Preprocess sesuai EfficientNet (scaling ke range yang sesuai)
        img_array = preprocess_input(img_array)
        
        return img_array
        
    except Exception as e:
        logger.error(f"Error saat preprocessing gambar: {str(e)}")
        raise ValueError(f"Gagal memproses gambar: {str(e)}")


# ==================== EVENT HANDLERS ====================

@app.on_event("startup")
async def startup_event():
    """
    Event handler yang dijalankan saat aplikasi startup.
    Memuat model ML ke memori.
    """
    logger.info("🚀 Memulai BeanScope API...")
    success = load_model()
    
    if not success:
        logger.warning("⚠️  API berjalan tanpa model! Jalankan model_builder.py terlebih dahulu.")
    else:
        logger.info("✅ BeanScope API siap menerima request!")


@app.on_event("shutdown")
async def shutdown_event():
    """
    Event handler yang dijalankan saat aplikasi shutdown.
    Cleanup resources.
    """
    logger.info("👋 Shutting down BeanScope API...")
    # Clear TensorFlow session untuk free memory
    if model is not None:
        tf.keras.backend.clear_session()


# ==================== API ENDPOINTS ====================

@app.get("/", tags=["Info"])
def root() -> Dict:
    """
    Endpoint root untuk mengecek status API.
    
    Returns:
        Dict dengan informasi status API
    """
    return {
        "app": "BeanScope Coffee Defect Detection API",
        "version": "1.0.0",
        "status": "Ready" if model is not None else "Model Not Loaded",
        "model_accuracy": "95.83%",
        "classes": CLASS_NAMES,
        "endpoints": {
            "predict": "/predict (POST)",
            "health": "/health (GET)",
            "docs": "/docs"
        }
    }


@app.get("/health", tags=["Info"])
def health_check() -> Dict:
    """
    Health check endpoint untuk monitoring.
    
    Returns:
        Dict dengan status kesehatan API
    """
    return {
        "status": "healthy" if model is not None else "unhealthy",
        "model_loaded": model is not None,
        "model_path": MODEL_PATH
    }


@app.post("/predict", tags=["Prediction"])
def predict(file: UploadFile = File(...)) -> Dict:
    """
    Endpoint untuk prediksi cacat biji kopi dari gambar.
    
    Args:
        file: File gambar yang diupload (JPG, PNG, dll)
        
    Returns:
        Dict berisi hasil prediksi dengan label, confidence, dan semua scores
        
    Raises:
        HTTPException: Jika terjadi error dalam proses prediksi
    """
    # Validasi 1: Cek apakah model sudah dimuat
    if model is None:
        logger.error("Attempt to predict without loaded model")
        raise HTTPException(
            status_code=503,
            detail="Model belum dimuat. Silakan restart server atau jalankan model_builder.py terlebih dahulu."
        )

    # Validasi 2: Cek tipe file
    if not file.content_type or not file.content_type.startswith("image/"):
        raise HTTPException(
            status_code=400,
            detail=f"Tipe file tidak valid. Harus berupa gambar (image/*). Diterima: {file.content_type}"
        )

    try:
        # 1. Baca file gambar
        logger.info(f"Processing image: {file.filename} ({file.content_type})")
        contents = file.file.read()
        
        # 2. Preprocessing gambar
        input_data = prepare_image(contents)
        logger.debug(f"Image preprocessed with shape: {input_data.shape}")
        
        # 3. Prediksi menggunakan model
        predictions = model.predict(input_data, verbose=0)  # verbose=0 untuk mengurangi output
        
        # 4. Ekstrak hasil prediksi
        scores = predictions[0]  # Shape: (5,) untuk 5 kelas
        predicted_idx = np.argmax(scores)
        predicted_label = CLASS_NAMES[predicted_idx]
        predicted_confidence = float(scores[predicted_idx])
        
        # 5. Buat response yang lebih informatif
        all_scores = {
            CLASS_NAMES[i]: {
                "percentage": f"{float(scores[i]) * 100:.2f}%",
                "probability": round(float(scores[i]), 4)
            }
            for i in range(len(CLASS_NAMES))
        }
        
        logger.info(f"Prediction: {predicted_label} ({predicted_confidence*100:.2f}%)")
        
        return {
            "status": "success",
            "prediction": {
                "label": predicted_label,
                "confidence": f"{predicted_confidence * 100:.2f}%",
                "probability": round(predicted_confidence, 4)
            },
            "all_predictions": all_scores,
            "metadata": {
                "filename": file.filename,
                "model_version": "EfficientNetB0-v1.0"
            }
        }

    except ValueError as ve:
        # Error dari preprocessing (ukuran file, format, dll)
        logger.warning(f"Validation error: {str(ve)}")
        raise HTTPException(status_code=400, detail=str(ve))
        
    except Exception as e:
        # Error tidak terduga
        logger.error(f"Unexpected error during prediction: {str(e)}", exc_info=True)
        raise HTTPException(
            status_code=500,
            detail=f"Terjadi kesalahan saat melakukan prediksi: {str(e)}"
        )
        
    finally:
        # Pastikan file ditutup
        file.file.close()


@app.post("/predict-batch", tags=["Prediction"])
async def predict_batch(file: UploadFile = File(...)) -> Dict:
    """
    Endpoint untuk prediksi batch dari file ZIP.
    ZIP harus berisi sub-folder dengan nama kelas sebagai label asli.
    Contoh:
      dataset.zip/
        Broken/img1.jpg
        Green/img2.jpg
        ...

    Returns:
        Dict berisi results (list), confusion_matrix (5x5), metrics (accuracy, per-class)
    """
    if model is None:
        raise HTTPException(status_code=503, detail="Model belum dimuat.")

    # Validasi tipe file
    if file.content_type not in ["application/zip", "application/x-zip-compressed",
                                  "application/octet-stream", "multipart/form-data"]:
        # coba cek ekstensi
        if not (file.filename or "").lower().endswith(".zip"):
            raise HTTPException(status_code=400, detail="File harus berupa ZIP.")

    zip_bytes = await file.read()
    tmp_dir = tempfile.mkdtemp()
    results: List[Dict] = []

    VALID_EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}

    try:
        with zipfile.ZipFile(io.BytesIO(zip_bytes)) as zf:
            zf.extractall(tmp_dir)

        # Kalau ZIP berisi satu folder root (mis. dataset/Broken, dataset/Green, ...),
        # turun satu level agar class_dir langsung menunjuk ke folder kelas.
        top_entries = [e for e in os.listdir(tmp_dir)]
        if len(top_entries) == 1 and os.path.isdir(os.path.join(tmp_dir, top_entries[0])):
            inner = os.path.join(tmp_dir, top_entries[0])
            # Pastikan folder tersebut berisi sub-folder kelas, bukan gambar langsung
            inner_entries = os.listdir(inner)
            has_class_folder = any(
                os.path.isdir(os.path.join(inner, e)) for e in inner_entries
            )
            if has_class_folder:
                tmp_dir = inner  # Gunakan folder di dalam sebagai root

        # Iterasi sub-folder sebagai ground truth label
        for true_label in CLASS_NAMES:
            class_dir = os.path.join(tmp_dir, true_label)
            if not os.path.isdir(class_dir):
                # Coba cari folder case-insensitive (normalkan spasi, jangan hapus)
                for d in os.listdir(tmp_dir):
                    if d.lower() == true_label.lower():
                        class_dir = os.path.join(tmp_dir, d)
                        break
                    # Juga coba tanpa spasi (fullblack vs full black)
                    if d.lower().replace(" ", "") == true_label.lower().replace(" ", ""):
                        class_dir = os.path.join(tmp_dir, d)
                        break
                else:
                    logger.warning(f"Folder untuk kelas '{true_label}' tidak ditemukan di ZIP.")
                    continue  # Folder tidak ada, skip

            for fname in os.listdir(class_dir):
                ext = os.path.splitext(fname)[1].lower()
                if ext not in VALID_EXTENSIONS:
                    continue
                fpath = os.path.join(class_dir, fname)
                try:
                    with open(fpath, "rb") as f:
                        img_bytes = f.read()
                    img_array = prepare_image(img_bytes)
                    preds = model.predict(img_array, verbose=0)[0]
                    pred_idx = int(np.argmax(preds))
                    pred_label = CLASS_NAMES[pred_idx]
                    confidence = float(preds[pred_idx])
                    results.append({
                        "filename": fname,
                        "true_label": true_label,
                        "predicted_label": pred_label,
                        "confidence": round(confidence * 100, 2),
                        "correct": pred_label == true_label,
                        "all_scores": {
                            CLASS_NAMES[i]: round(float(preds[i]) * 100, 2)
                            for i in range(len(CLASS_NAMES))
                        }
                    })
                except Exception as img_err:
                    logger.warning(f"Skip {fname}: {img_err}")
                    results.append({
                        "filename": fname,
                        "true_label": true_label,
                        "predicted_label": "Error",
                        "confidence": 0.0,
                        "correct": False,
                        "all_scores": {},
                        "error": str(img_err)
                    })

        if not results:
            raise HTTPException(status_code=400,
                detail="Tidak ada gambar valid ditemukan dalam ZIP. "
                       "Pastikan ZIP berisi sub-folder dengan nama kelas.")

        # Hitung confusion matrix (5x5)
        n = len(CLASS_NAMES)
        cm = [[0] * n for _ in range(n)]
        for r in results:
            if r["predicted_label"] in CLASS_NAMES and r["true_label"] in CLASS_NAMES:
                ti = CLASS_NAMES.index(r["true_label"])
                pi = CLASS_NAMES.index(r["predicted_label"])
                cm[ti][pi] += 1

        # Hitung metrics per kelas
        metrics_per_class = {}
        total_correct = sum(1 for r in results if r["correct"])
        total = len(results)
        accuracy = round(total_correct / total * 100, 2) if total > 0 else 0.0

        for i, cls in enumerate(CLASS_NAMES):
            tp = cm[i][i]
            fp = sum(cm[j][i] for j in range(n) if j != i)
            fn = sum(cm[i][j] for j in range(n) if j != i)
            precision = tp / (tp + fp) if (tp + fp) > 0 else 0.0
            recall = tp / (tp + fn) if (tp + fn) > 0 else 0.0
            f1 = (2 * precision * recall / (precision + recall)
                  if (precision + recall) > 0 else 0.0)
            metrics_per_class[cls] = {
                "precision": round(precision * 100, 2),
                "recall": round(recall * 100, 2),
                "f1_score": round(f1 * 100, 2),
                "support": sum(cm[i])
            }

        return {
            "status": "success",
            "total_images": total,
            "correct_predictions": total_correct,
            "accuracy": accuracy,
            "class_names": CLASS_NAMES,
            "results": results,
            "confusion_matrix": cm,
            "metrics": metrics_per_class
        }

    except HTTPException:
        raise
    except zipfile.BadZipFile:
        raise HTTPException(status_code=400, detail="File ZIP tidak valid atau rusak.")
    except Exception as e:
        logger.error(f"Error predict-batch: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=f"Kesalahan saat batch prediksi: {str(e)}")
    finally:
        shutil.rmtree(tmp_dir, ignore_errors=True)


# ==================== EXCEPTION HANDLERS ====================

@app.exception_handler(Exception)
async def global_exception_handler(request, exc):
    """
    Global exception handler untuk menangkap semua error yang tidak tertangani.
    """
    logger.error(f"Unhandled exception: {str(exc)}", exc_info=True)
    return JSONResponse(
        status_code=500,
        content={
            "status": "error",
            "message": "Terjadi kesalahan internal pada server",
            "detail": str(exc)
        }
    )


# ==================== MAIN ====================

if __name__ == "__main__":
    import uvicorn
    
    logger.info("Starting BeanScope API server...")
    
    # Jalankan server dengan konfigurasi optimal
    uvicorn.run(
        app,
        host="127.0.0.1",
        port=8000,
        log_level="info",
        # workers=1,  # Untuk production, bisa ditingkatkan sesuai CPU cores
    )
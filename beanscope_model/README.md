# BeanScope Coffee Defect Detection System

Sistem deteksi cacat biji kopi menggunakan Deep Learning (EfficientNetB0) dengan akurasi 95.83%.

## 📋 Deskripsi

BeanScope adalah sistem klasifikasi cacat biji kopi yang menggunakan Transfer Learning dengan model EfficientNetB0. Sistem ini dapat mendeteksi 5 jenis cacat pada biji kopi:

1. **Broken** - Biji kopi pecah/patah
2. **Fullblack** - Biji kopi hitam sempurna  
3. **Fungus Damage** - Kerusakan akibat jamur
4. **Green** - Biji kopi masih hijau (belum matang)
5. **Insect Damage** - Kerusakan akibat serangga

## 🔧 Masalah yang Telah Diperbaiki

### Error yang Terjadi
```
❌ Gagal memuat model: Layer "dense" expects 1 input(s), but it received 2 input tensors.
```

### Penyebab Masalah
Model yang disimpan memiliki arsitektur yang bermasalah, kemungkinan karena:
1. Ada custom layer atau callback yang mengubah graf komputasi saat training
2. Model disimpan dengan versi TensorFlow yang berbeda
3. Ada layer yang tidak kompatibel saat loading

### Solusi
Kami membuat script `model_builder.py` yang:
1. Membangun ulang arsitektur model dengan benar
2. Mentransfer weights dari model lama ke model baru
3. Menyimpan model dengan format yang clean dan kompatibel

## 🚀 Cara Menggunakan

### 1. Perbaiki Model (Jalankan Sekali Saja)

```bash
cd "D:\Skripsi\bestmodel\BeanScope\beanscope_model"
python model_builder.py
```

Script ini akan:
- ✅ Membaca model lama yang bermasalah
- ✅ Membangun arsitektur baru yang benar
- ✅ Mentransfer semua weights
- ✅ Menyimpan sebagai `best_hyperparameter_model_fixed.keras`

### 2. Jalankan API Server

```bash
python main.py
```

Server akan:
- ✅ Memuat model yang telah diperbaiki
- ✅ Berjalan di `http://127.0.0.1:8000`
- ✅ Menyediakan API untuk prediksi gambar

### 3. Test API

Buka browser dan akses `http://127.0.0.1:8000/docs` untuk dokumentasi interaktif (Swagger UI).

Atau test dengan curl:
```bash
curl -X 'POST' \
  'http://127.0.0.1:8000/predict' \
  -H 'accept: application/json' \
  -H 'Content-Type: multipart/form-data' \
  -F 'file=@path/to/your/coffee_bean_image.jpg'
```

## 📁 Struktur File

```
BeanScope/
└── beanscope_model/
    ├── main.py                              # FastAPI server (SUDAH DIPERBAIKI ✅)
    ├── model_builder.py                     # Script perbaikan model (BARU ✨)
    ├── README.md                            # File ini
    └── model/
        ├── best_hyperparameter_model.keras         # Model lama (bermasalah)
        └── best_hyperparameter_model_fixed.keras   # Model baru (sudah diperbaiki ✅)
```

## 🎯 Arsitektur Model

```
Input (224x224x3)
    ↓
EfficientNetB0 (pre-trained on ImageNet)
    ↓
GlobalAveragePooling2D  → (None, 1280)
    ↓
GaussianNoise(0.1)      → Regularisasi
    ↓
Dense(256) + L2(0.001)  → Feature extraction
    ↓
BatchNormalization      → Normalisasi
    ↓
ReLU Activation         → Non-linearity
    ↓
Dropout(0.4)            → Regularisasi
    ↓
Dense(5, softmax)       → Output (5 kelas)
```

## 📊 Performa Model

- **Akurasi Validasi**: 95.83%
- **Dataset Training**: 960 gambar (5 kelas)
- **Dataset Validasi**: 240 gambar
- **Framework**: TensorFlow/Keras
- **Base Model**: EfficientNetB0

## 🔄 Perbaikan yang Dilakukan

### 1. Model Architecture (`model_builder.py`)
- ✅ Clean architecture tanpa custom layers bermasalah
- ✅ Proper layer naming untuk debugging
- ✅ Transfer weights dari model lama
- ✅ Validasi model dapat dimuat kembali

### 2. API Server (`main.py`)
- ✅ Logging yang proper untuk debugging
- ✅ Error handling yang comprehensive
- ✅ Fallback ke model lama jika model baru belum ada
- ✅ Type hints untuk better code quality
- ✅ Validasi input yang lebih baik
- ✅ Response format yang lebih informatif
- ✅ Health check endpoint
- ✅ Auto-generated API documentation

### 3. Code Quality
- ✅ Dokumentasi lengkap (docstrings)
- ✅ Separation of concerns
- ✅ Clean code principles
- ✅ Better variable naming
- ✅ Structured logging

## 🔍 Debugging Tips

### Cek Status Model
```python
# Di Python REPL
import tensorflow as tf
model = tf.keras.models.load_model("model/best_hyperparameter_model_fixed.keras", compile=False)
model.summary()
```

### Cek Logs
Server akan menampilkan log seperti:
```
INFO - 🚀 Memulai BeanScope API...
INFO - Mencoba memuat model dari: ...
INFO - ✅ Berhasil memuat model dari: ...
INFO - 📊 Total parameter: 4,059,693
INFO - ✅ BeanScope API siap menerima request!
```

## 🐛 Troubleshooting

### Jika masih error saat load model:

1. **Pastikan Python environment konsisten**
   ```bash
   pip list | findstr tensorflow
   ```

2. **Rebuild model dari scratch**
   ```bash
   python model_builder.py
   ```

3. **Cek file dependencies**
   - TensorFlow >= 2.x
   - Pillow
   - NumPy
   - FastAPI
   - Uvicorn

### Jika predict error:

1. Cek format gambar (harus JPG/PNG)
2. Cek ukuran file (max 10MB)
3. Lihat logs untuk detail error

## 📝 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/` | GET | Info status API |
| `/health` | GET | Health check |
| `/predict` | POST | Prediksi dari gambar |
| `/docs` | GET | Swagger UI documentation |

### Contoh Response `/predict`:

```json
{
  "status": "success",
  "prediction": {
    "label": "Broken",
    "confidence": "96.45%",
    "probability": 0.9645
  },
  "all_predictions": {
    "Broken": {
      "percentage": "96.45%",
      "probability": 0.9645
    },
    "Fullblack": {
      "percentage": "2.10%",
      "probability": 0.0210
    },
    ...
  },
  "metadata": {
    "filename": "coffee_bean.jpg",
    "model_version": "EfficientNetB0-v1.0"
  }
}
```

## 👨‍💻 Development

### Requirements
```bash
pip install tensorflow pillow numpy fastapi uvicorn python-multipart
```

### Run in Development Mode
```bash
uvicorn main:app --reload --host 127.0.0.1 --port 8000
```

## 📄 License

Copyright © 2024 BeanScope Project

---

**Dibuat dengan ❤️ untuk deteksi kualitas biji kopi**

"""
Script untuk membangun dan menyimpan ulang model BeanScope Coffee Defect Classifier
dengan arsitektur yang benar dan mudah dimuat kembali.
"""

import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'  # Mengurangi warning TensorFlow

import tensorflow as tf
from tensorflow.keras.applications import EfficientNetB0
from tensorflow.keras.layers import (
    Dense, GlobalAveragePooling2D, Dropout, 
    BatchNormalization, Activation, GaussianNoise
)
from tensorflow.keras.models import Model
from tensorflow.keras import regularizers

# Konfigurasi
NUM_CLASSES = 5  # Broken, Fullblack, Fungus Damage, Green, Insect Damage
IMG_SIZE = (224, 224)
OLD_MODEL_PATH = r"D:\Skripsi\bestmodel\BeanScope\beanscope_model\model\best_hyperparameter_model.keras"
NEW_MODEL_PATH = r"D:\Skripsi\bestmodel\BeanScope\beanscope_model\model\best_hyperparameter_model_fixed.keras"


def build_model_architecture():
    """
    Membangun arsitektur model EfficientNetB0 dengan custom head yang benar.
    
    Returns:
        tf.keras.Model: Model yang siap dilatih atau dimuat weights-nya
    """
    # 1. Load EfficientNetB0 sebagai base model (tanpa top layer)
    base_model = EfficientNetB0(
        weights='imagenet',
        include_top=False,
        input_shape=IMG_SIZE + (3,)
    )
    
    # 2. Bangun Head Model yang dioptimalkan
    x = base_model.output
    x = GlobalAveragePooling2D(name='global_avg_pool')(x)  # (None, 1280)
    
    # Noise ringan untuk regularisasi
    x = GaussianNoise(0.1, name='gaussian_noise')(x)
    
    # Dense layer dengan L2 regularization
    x = Dense(
        256, 
        kernel_regularizer=regularizers.l2(0.001),
        name='dense_256'
    )(x)
    x = BatchNormalization(name='batch_norm')(x)
    x = Activation('relu', name='relu_activation')(x)
    x = Dropout(0.4, name='dropout')(x)
    
    # Output layer
    predictions = Dense(
        NUM_CLASSES, 
        activation='softmax',
        name='output_layer'
    )(x)
    
    # Buat model
    model = Model(inputs=base_model.input, outputs=predictions, name='BeanScope_Model')
    
    return model, base_model


def rebuild_and_save_model():
    """
    Memuat weights dari model lama dan menyimpan ulang dengan arsitektur yang benar.
    """
    print("=" * 70)
    print("BeanScope Model Rebuilder - Perbaikan Arsitektur Model")
    print("=" * 70)
    
    # 1. Cek apakah file model lama ada
    if not os.path.exists(OLD_MODEL_PATH):
        print(f"[ERROR] File model tidak ditemukan: {OLD_MODEL_PATH}")
        return False
    
    print(f"\n[OK] File model ditemukan: {OLD_MODEL_PATH}")
    
    try:
        # 2. Muat model lama dengan safe_mode=False
        print("\n[INFO] Memuat model lama...")
        old_model = tf.keras.models.load_model(
            OLD_MODEL_PATH,
            compile=False,
            safe_mode=False
        )
        print("[OK] Model lama berhasil dimuat")
        
        # 3. Bangun arsitektur baru yang benar
        print("\n[INFO] Membangun arsitektur model baru...")
        new_model, base_model = build_model_architecture()
        print("[OK] Arsitektur model baru selesai dibangun")
        
        # 4. Transfer weights dari old_model ke new_model
        print("\n[INFO] Mentransfer weights dari model lama ke model baru...")
        
        # Buat mapping layer berdasarkan nama
        old_layers = {layer.name: layer for layer in old_model.layers}
        transferred_count = 0
        
        for new_layer in new_model.layers:
            if new_layer.name in old_layers:
                old_layer = old_layers[new_layer.name]
                # Pastikan shape weights sama
                if hasattr(old_layer, 'get_weights') and len(old_layer.get_weights()) > 0:
                    try:
                        old_weights = old_layer.get_weights()
                        new_layer.set_weights(old_weights)
                        transferred_count += 1
                    except Exception as e:
                        print(f"[WARNING] Tidak bisa transfer weights untuk layer '{new_layer.name}': {e}")
        
        print(f"[OK] Berhasil mentransfer weights untuk {transferred_count} layer")
        
        # 5. Freeze base model (transfer learning)
        base_model.trainable = False
        print(f"\n[INFO] Base model (EfficientNetB0) dibekukan untuk transfer learning")
        
        # 6. Kompilasi model dengan konfigurasi yang sama
        print("\n[INFO] Mengkompilasi model...")
        new_model.compile(
            optimizer=tf.keras.optimizers.Adam(learning_rate=1e-4),
            loss=tf.keras.losses.CategoricalCrossentropy(label_smoothing=0.1),
            metrics=['accuracy']
        )
        print("[OK] Model berhasil dikompilasi")
        
        # 7. Simpan model baru
        print(f"\n[INFO] Menyimpan model yang sudah diperbaiki ke: {NEW_MODEL_PATH}")
        new_model.save(NEW_MODEL_PATH)
        print("[OK] Model baru berhasil disimpan!")
        
        # 8. Verifikasi dengan memuat ulang
        print("\n[INFO] Memverifikasi model yang baru disimpan...")
        test_model = tf.keras.models.load_model(NEW_MODEL_PATH, compile=False)
        print("[OK] Model baru dapat dimuat kembali dengan sukses!")
        
        # 9. Tampilkan summary
        print("\n[INFO] Summary Model Baru:")
        print("=" * 70)
        new_model.summary()
        
        print("\n" + "=" * 70)
        print("SELESAI! Model berhasil diperbaiki dan disimpan.")
        print("=" * 70)
        print(f"\nFile baru: {NEW_MODEL_PATH}")
        print(f"Total parameter: {new_model.count_params():,}")
        print("\n[TIP] Gunakan model baru ini di main.py dengan mengupdate MODEL_PATH!")
        
        return True
        
    except Exception as e:
        print(f"\n[ERROR] Error saat membangun ulang model: {str(e)}")
        print(f"\nDetail error:\n{e}")
        import traceback
        traceback.print_exc()
        return False



if __name__ == "__main__":
    success = rebuild_and_save_model()
    
    if not success:
        print("\n[TIP] Alternatif: Jika error persist, Anda perlu melatih ulang model dari awal.")


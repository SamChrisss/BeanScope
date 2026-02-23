"""
Alternative Model Builder - Membuat model dari scratch atau dari .h5
==================================================================
Script ini membuat model baru dengan arsitektur yang benar.
Jika tersedia file .h5, akan mencoba memuat weights dari sana.
"""

import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'

import tensorflow as tf
from tensorflow.keras.applications import EfficientNetB0
from tensorflow.keras.layers import (
    Dense, GlobalAveragePooling2D, Dropout,
    BatchNormalization, Activation, GaussianNoise
)
from tensorflow.keras.models import Model
from tensorflow.keras import regularizers

# Konfigurasi
NUM_CLASSES = 5
IMG_SIZE = (224, 224)
H5_MODEL_PATH = r"D:\Skripsi\bestmodel\BeanScope\beanscope_model\model\best_hyperparameter_model.h5"
NEW_MODEL_PATH = r"D:\Skripsi\bestmodel\BeanScope\beanscope_model\model\best_hyperparameter_model_fixed.keras"


def build_clean_model():
    """Membangun model dengan arsitektur yang bersih dan benar."""
    print("\n[INFO] Membangun arsitektur model EfficientNetB0...")
    
    # 1. Base model dari EfficientNetB0
    base_model = EfficientNetB0(
        weights='imagenet',
        include_top=False,
        input_shape=IMG_SIZE + (3,)
    )
    
    # 2. Custom head
    x = base_model.output
    x = GlobalAveragePooling2D(name='global_avg_pool')(x)
    x = GaussianNoise(0.1, name='gaussian_noise')(x)
    x = Dense(256, kernel_regularizer=regularizers.l2(0.001), name='dense_256')(x)
    x = BatchNormalization(name='batch_norm')(x)
    x = Activation('relu', name='relu_activation')(x)
    x = Dropout(0.4, name='dropout')(x)
    predictions = Dense(NUM_CLASSES, activation='softmax', name='output_layer')(x)
    
    # 3. Buat model
    model = Model(inputs=base_model.input, outputs=predictions, name='BeanScope_Model')
    
    # 4. Freeze base model
    base_model.trainable = False
    
    # 5. Compile
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=1e-4),
        loss=tf.keras.losses.CategoricalCrossentropy(label_smoothing=0.1),
        metrics=['accuracy']
    )
    
    print("[OK] Model berhasil dibangun")
    print(f"[INFO] Total parameter: {model.count_params():,}")
    
    return model


def try_load_from_h5(model):
    """Mencoba memuat weights dari file .h5 jika ada."""
    if not os.path.exists(H5_MODEL_PATH):
        print(f"[WARNING] File .h5 tidak ditemukan: {H5_MODEL_PATH}")
        print("[INFO] Model akan menggunakan pre-trained weights dari ImageNet saja")
        return False
    
    try:
        print(f"\n[INFO] Mencoba memuat weights dari .h5...")
        print(f"[INFO] File: {H5_MODEL_PATH}")
        
        # Load model dari .h5
        h5_model = tf.keras.models.load_model(H5_MODEL_PATH, compile=False)
        print("[OK] Model .h5 berhasil dimuat")
        
        # Transfer weights
        transferred = 0
        skipped = 0
        
        h5_layers = {layer.name: layer for layer in h5_model.layers}
        
        for new_layer in model.layers:
            if new_layer.name in h5_layers:
                h5_layer = h5_layers[new_layer.name]
                if hasattr(h5_layer, 'get_weights') and len(h5_layer.get_weights()) > 0:
                    try:
                        weights = h5_layer.get_weights()
                        new_layer.set_weights(weights)
                        transferred += 1
                    except Exception as e:
                        print(f"[WARNING] Skip layer '{new_layer.name}': {str(e)[:50]}")
                        skipped += 1
        
        print(f"[OK] Transferred {transferred} layers, skipped {skipped} layers")
        return True
        
    except Exception as e:
        print(f"[ERROR] Gagal memuat dari .h5: {str(e)}")
        print("[INFO] Model akan menggunakan pre-trained weights dari ImageNet saja")
        return False


def main():
    print("=" * 70)
    print("BeanScope Model Builder - Alternative Approach")
    print("=" * 70)
    
    # 1. Build clean model
    model = build_clean_model()
    
    # 2. Try to load weights from .h5
    weights_loaded = try_load_from_h5(model)
    
    # 3. Save the new model
    print(f"\n[INFO] Menyimpan model ke: {NEW_MODEL_PATH}")
    model.save(NEW_MODEL_PATH)
    print("[OK] Model berhasil disimpan!")
    
    # 4. Verify
    print("\n[INFO] Memverifikasi model...")
    test_model = tf.keras.models.load_model(NEW_MODEL_PATH, compile=False)
    print("[OK] Model dapat dimuat kembali dengan sukses!")
    
    # 5. Summary
    print("\n[INFO] Model Summary:")
    print("=" * 70)
    model.summary()
    
    print("\n" + "=" * 70)
    if weights_loaded:
        print("SELESAI! Model berhasil dibuat dengan weights dari .h5")
    else:
        print("SELESAI! Model berhasil dibuat dengan ImageNet weights")
        print("\n[WARNING] Untuk performa optimal, Anda perlu:")
        print("  1. Melatih ulang model ini, ATAU")
        print("  2. Fine-tune dengan dataset Anda")
    print("=" * 70)
    print(f"\nFile output: {NEW_MODEL_PATH}")
    print(f"Total parameter: {model.count_params():,}")
    print("\n[TIP] Update MODEL_PATH di main.py untuk menggunakan model ini!")
    

if __name__ == "__main__":
    main()

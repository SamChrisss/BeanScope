# -*- coding: utf-8 -*-
"""
Script untuk test API BeanScope secara cepat
Memverifikasi bahwa model bisa di-load dan melakukan prediksi
"""

import os
import sys
import tensorflow as tf
import numpy as np

# Path ke model
MODEL_PATH = r"D:\Skripsi\bestmodel\BeanScope\beanscope_model\model\best_hyperparameter_model_fixed.keras"

# Class names yang benar
CLASS_NAMES = [
    "Broken",
    "Full Black",
    "Fungus Damange",
    "Green",
    "Insect Damage"
]

print("=" * 70)
print("TEST API BEANSCOPE - VERIFIKASI MODEL")
print("=" * 70)

# 1. Test load model
print("\n[1/3] Testing load model...")
try:
    model = tf.keras.models.load_model(MODEL_PATH, compile=False)
    print(f"[OK] Model berhasil di-load!")
    print(f"     Total parameters: {model.count_params():,}")
except Exception as e:
    print(f"[ERROR] Gagal load model: {e}")
    sys.exit(1)

# 2. Test prediction dengan dummy input
print("\n[2/3] Testing prediction dengan dummy input...")
try:
    dummy_input = np.random.rand(1, 224, 224, 3).astype(np.float32)
    
    # Preprocess seperti di API
    from tensorflow.keras.applications.efficientnet import preprocess_input
    dummy_preprocessed = preprocess_input(dummy_input)
    
    # Predict
    predictions = model.predict(dummy_preprocessed, verbose=0)
    
    print(f"[OK] Prediksi berhasil!")
    print(f"     Output shape: {predictions.shape}")
    print(f"     Sum of probabilities: {predictions.sum():.4f} (should be ~1.0)")
    
    # Tampilkan hasil
    scores = predictions[0]
    predicted_idx = np.argmax(scores)
    predicted_class = CLASS_NAMES[predicted_idx]
    confidence = scores[predicted_idx]
    
    print(f"\n     Predicted class: {predicted_class}")
    print(f"     Confidence: {confidence * 100:.2f}%")
    
except Exception as e:
    print(f"[ERROR] Gagal melakukan prediksi: {e}")
    sys.exit(1)

# 3. Verifikasi class names
print("\n[3/3] Verifikasi class names...")
print(f"[OK] Jumlah kelas: {len(CLASS_NAMES)}")
print("     Urutan kelas:")
for i, class_name in enumerate(CLASS_NAMES):
    print(f"     {i}: {class_name}")

print("\n" + "=" * 70)
print("[SUCCESS] SEMUA TEST BERHASIL!")
print("=" * 70)
print("\nAPI siap digunakan dengan konfigurasi:")
print(f"- Model: best_hyperparameter_model_fixed.keras")
print(f"- Total parameters: {model.count_params():,}")
print(f"- Class names: {CLASS_NAMES}")
print("\nJalankan API dengan: python main.py")

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeanScope - Coffee Bean Defect Prediction</title>
    <link rel="icon" type="image/png" href="{{ asset('Image/BeanScopelogoo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @vite('resources/css/style.css')
</head>
<body class="predict-page" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset('Image/home.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
    @include('components.navbar')
    
    <div class="container main-container">        <!-- Header -->
        <div class="text-center mb-5">
            <!--<img src="{{ asset('Image/LogoOval.png') }}" alt="BeanScope Logo" class="hero-logo">-->
            <h1 class="header-title display-4 fw-bold">
            <img src="{{ asset('Image/LogoOval.png') }}" alt="BeanScope Logo" class="hero-logo">
            <br>BeanScope
            </h1>
            <p class="text-white fs-5">Deteksi Cacat Biji Kopi dengan AI</p>
        </div>

        <!-- Upload Form Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h4 class="card-title text-center mb-4">
                            <i class="bi bi-cloud-upload"></i> Upload Gambar Biji Kopi
                        </h4>

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('predict') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="upload-area mb-4" onclick="document.getElementById('imageInput').click()">
                                <i class="bi bi-image"></i>
                                <h5>Klik untuk memilih gambar</h5>
                                <p class="text-muted mb-0">Format: JPG, PNG (Max: 2MB)</p>
                                <input type="file" 
                                       id="imageInput" 
                                       name="image" 
                                       accept="image/*" 
                                       class="d-none" 
                                       required
                                       onchange="previewImage(this)">
                            </div>

                            <div id="imagePreview" class="text-center mb-4" style="display: none;">
                                <img id="preview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                                <p class="mt-2 text-muted" id="fileName"></p>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-magic"></i> Analisis Gambar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Section -->
        @if(isset($result))
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="card result-card border-success">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h5 class="text-muted">
                                <i class="bi bi-check-circle-fill text-success"></i> Hasil Analisis
                            </h5>
                            <h2 class="text-success fw-bold">{{ $result['label'] }}</h2>
                            <span class="badge bg-success fs-6">Confidence: {{ $result['confidence'] }}</span>
                        </div>

                        @php
                            // Determine suitability based on prediction
                            $greenScore = floatval(str_replace('%', '', $all_scores['Green'] ?? '0'));
                            $isSuitable = $greenScore > 50;
                            
                            // Check confidence threshold
                            $maxConfidence = floatval(str_replace('%', '', $result['confidence']));
                            $isLowConfidence = $maxConfidence < 80;
                        @endphp

                        <!-- Low Confidence Warning -->
                        @if($isLowConfidence)
                        <div class="alert alert-warning text-center mb-4" role="alert">
                            <h4 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Peringatan: Confidence Rendah
                            </h4>
                            <p class="mb-0">
                                <strong>Kemungkinan bukan biji kopi!</strong><br>
                                Prediksi memiliki confidence {{ $result['confidence'] }} yang rendah. 
                                Gambar yang diupload mungkin bukan biji kopi.
                                Silakan upload gambar biji kopi yang lebih jelas.
                            </p>
                        </div>
                        @endif

                        <!-- Suitability Status -->
                        <div class="alert {{ $isSuitable ? 'alert-success' : 'alert-danger' }} text-center mb-4" role="alert">
                            <h4 class="alert-heading mb-2">
                                <i class="bi {{ $isSuitable ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                Status Kelayakan Biji Kopi
                            </h4>
                            <h3 class="mb-2">
                                @if($isSuitable)
                                    <strong>Biji Layak Pakai</strong>
                                @else
                                    <strong>Tidak Layak</strong>
                                @endif
                            </h3>
                            <p class="mb-0">
                                @if($isSuitable)
                                    Biji kopi terdeteksi sebagai Green dengan persentase {{ $all_scores['Green'] }}, sehingga layak untuk digunakan.
                                @else
                                    Biji kopi terdeteksi memiliki cacat ({{ $result['label'] }}) dengan persentase {{ $result['confidence'] }}, sehingga tidak layak untuk digunakan.
                                @endif
                            </p>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-5 text-center mb-3">
                                <img src="{{ asset($image_path) }}" 
                                     class="img-fluid rounded border shadow-sm" 
                                     style="max-height: 250px;"
                                     alt="Uploaded Image">
                            </div>

                            <div class="col-md-7">
                                <h6 class="mb-3 fw-bold">
                                    <i class="bi bi-bar-chart-fill"></i> Detail Semua Kelas:
                                </h6>
                                @foreach($all_scores as $label => $score)
                                    @php
                                        // Menghilangkan tanda '%' untuk kebutuhan width progress bar
                                        $val = str_replace('%', '', $score);
                                        $isTop = ($label == $result['label']);
                                    @endphp
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="fw-bold {{ $isTop ? 'text-success' : '' }}">
                                                @if($isTop) <i class="bi bi-star-fill"></i> @endif
                                                {{ $label }}
                                            </small>
                                            <small class="text-muted fw-bold">{{ $score }}</small>
                                        </div>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar {{ $isTop ? 'bg-success' : 'bg-secondary' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $val }}%" 
                                                 aria-valuenow="{{ $val }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('predict.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise"></i> Analisis Gambar Lain
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Info Card -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="mb-2"><i class="bi bi-info-circle-fill text-primary"></i> Kelas yang Dapat Dideteksi:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0 small">
                                    <li><strong>Broken:</strong> Biji kopi pecah/patah</li>
                                    <li><strong>Full Black:</strong> Biji kopi hitam sempurna</li>
                                    <li><strong>Fungus Damage:</strong> Kerusakan jamur</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0 small">
                                    <li><strong>Green:</strong> Biji kopi masih hijau</li>
                                    <li><strong>Insect Damage:</strong> Kerusakan serangga</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite('resources/js/script.js')
</body>
</html>
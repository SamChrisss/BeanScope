<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeanScope - AI-Based Green Coffee Bean Defect Detection</title>
    <link rel="icon" type="image/png" href="{{ asset('Image/BeanScopelogoo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/style.css')
</head>
<body class="home-page">
    @include('components.navbar')
    
    <!-- Hero Section -->
    <section class="hero-section" style="background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('{{ asset('Image/home.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <!--<img src="{{ asset('Image/BeanScopelogoo.png') }}" alt="BeanScope Logo" class="hero-logo">-->
                    <h1 class="hero-title">BeanScope – AI-Based Green Coffee Bean Defect Classification</h1>
                    <p class="hero-subtitle">
                        Helping coffee quality control with advanced artificial intelligence 
                    </p>
                    <div class="hero-cta">
                        <a href="{{ route('predict.index') }}" class="btn btn-light btn-hero me-3">
                            <i class="bi bi-magic"></i> Try Analyze
                        </a>
                        <a href="#about" class="btn btn-outline-light btn-hero">
                            <i class="bi bi-info-circle"></i> Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <!--<img src="{{ asset('Image/home.jpg') }}" alt="BeanScope" class="hero-image img-fluid" style="max-width: 400px; opacity: 0.9;">-->
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="section-title">About BeanScope</h2>
                    </div>
                    <div class="about-text">
                        <p class="mb-4">
                            <strong>BeanScope</strong> is an innovative AI-powered system designed to detect defects in green coffee beans through advanced image classification technology. Our mission is to help coffee producers, processors, and quality control specialists maintain the standards of coffee bean quality.
                        </p>
                        <p class="mb-4">
                            Using state-of-the-art deep learning algorithms, BeanScope can accurately classify various types of defects including broken beans, full black beans, fungus damage, green beans, and insect damage. This automated approach significantly reduces manual inspection time while improving accuracy and consistency.
                        </p>
                        <p>
                            Whether you're a small-scale coffee farmer or a large processing facility, BeanScope provides fast, reliable, and accessible quality control solutions that help ensure only the finest coffee beans reach the market.
                        </p>
                        <p>
                            Here are some examples of defective coffee beans that are often found.
                        </p>

                        <div class="mt-5">
                            <h4 class="mb-4 fw-bold" style="color: var(--primary-color);">
                                <i class="bi bi-images"></i> Coffee Bean Defect Examples
                            </h4>
                            <div class="row g-3">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="defect-card">
                                        <img src="{{ asset('Image/green (50).png') }}" alt="Green Bean" class="img-fluid rounded">
                                        <p class="defect-label">Green</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="defect-card">
                                        <img src="{{ asset('Image/Broken (34).jpg') }}" alt="Broken Bean" class="img-fluid rounded">
                                        <p class="defect-label">Broken</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="defect-card">
                                        <img src="{{ asset('Image/Full Black (1).jpg') }}" alt="Full Black Bean" class="img-fluid rounded">
                                        <p class="defect-label">Full Black</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="defect-card">
                                        <img src="{{ asset('Image/Fungus Damage (7).jpg') }}" alt="Fungus Damage" class="img-fluid rounded">
                                        <p class="defect-label">Fungus Damage</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="defect-card">
                                        <img src="{{ asset('Image/Insect Damage (4).jpg') }}" alt="Insect Damage" class="img-fluid rounded">
                                        <p class="defect-label">Insect Damage</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Key Features</h2>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <h3 class="feature-title">AI Image Classification</h3>
                        <p class="feature-text">
                            Powered by advanced deep learning models trained on thousands of coffee bean images, ensuring high accuracy and reliability in defect detection.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="feature-title">Defect Detection on Green Coffee Beans</h3>
                        <p class="feature-text">
                            Identifies multiple defect types including broken, full black, fungus damage, green beans, and insect damage with detailed confidence scores.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h3 class="feature-title">Fast & Accurate Prediction</h3>
                        <p class="feature-text">
                            Get instant results with high precision. Our optimized AI model processes images in seconds.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} BeanScope. All rights reserved. | AI-Based Coffee Bean Quality Control System</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite('resources/js/script.js')
</body>
</html>

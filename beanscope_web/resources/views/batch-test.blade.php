<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeanScope - Batch Testing</title>
    <link rel="icon" type="image/png" href="{{ asset('Image/BeanScopelogoo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @vite('resources/css/style.css')
</head>
<body class="batch-page" style="background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('{{ asset('Image/home.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
    @include('components.navbar')

    <div class="container py-5">

        {{-- ===== Header ===== --}}
        <div class="text-center mb-5" id="print-batch-header">
            <img src="{{ asset('Image/LogoOval.png') }}" alt="BeanScope Logo" style="height:70px;">
            <h1 class="text-white fw-bold display-5 mt-2">Batch Testing</h1>
            <p class="text-white-50 fs-5">Upload ZIP dataset untuk menguji performa model secara batch</p>
        </div>

        {{-- ===== Alerts ===== --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ===== Upload Card ===== --}}
        <div class="row justify-content-center mb-4 no-print">
            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-1"><i class="bi bi-archive-fill text-warning"></i> Upload File ZIP</h5>
                    <p class="text-muted small mb-3">
                        ZIP harus berisi sub-folder bernama sesuai kelas:
                        <code>Broken</code>, <code>Full Black</code>, <code>Fungus Damage</code>,
                        <code>Green</code>, <code>Insect Damage</code>
                    </p>

                    <form action="{{ route('batch-test.process') }}" method="POST"
                          enctype="multipart/form-data" id="batchForm">
                        @csrf

                        {{-- Drag & drop zone --}}
                        <div class="upload-zone mb-3" id="dropZone"
                             onclick="document.getElementById('zipInput').click()">
                            <i class="bi bi-file-earmark-zip-fill"></i>
                            <h5 class="mt-2 mb-1">Klik atau seret ZIP ke sini</h5>
                            <p class="text-muted small mb-0">Format: .zip &nbsp;|&nbsp; Maks: 100 MB</p>
                            <input type="file" id="zipInput" name="zipfile"
                                   accept=".zip" class="d-none" required
                                   onchange="onFileSelected(this)">
                        </div>

                        {{-- File info --}}
                        <div id="fileInfo" class="d-none alert alert-info py-2 mb-3">
                            <i class="bi bi-file-earmark-zip"></i>
                            <span id="fileName"></span> &nbsp;
                            <span class="text-muted small" id="fileSize"></span>
                        </div>

                        {{-- Upload progress --}}
                        <div id="progressWrap" class="d-none mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span id="progressLabel">Mengirim &amp; memproses ...</span>
                                <span id="progressPct">0%</span>
                            </div>
                            <div class="progress" style="height:20px; border-radius:10px;">
                                <div id="progressBar"
                                     class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                                     role="progressbar" style="width:0%"></div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-warning btn-lg px-5 fw-bold"
                                    id="submitBtn" disabled>
                                <i class="bi bi-play-circle-fill"></i> Mulai Testing
                            </button>
                        </div>
                    </form>

                    {{-- Format guide --}}
                    <div class="mt-3 p-3 rounded" style="background:#f9f3ee;">
                        <h6 class="mb-2"><i class="bi bi-info-circle-fill text-primary"></i> Struktur ZIP yang benar:</h6>
                        <pre class="mb-0 small text-muted" style="line-height:1.7">dataset_test.zip
├── Broken/
│   ├── img1.jpg
│   └── img2.png
├── Full Black/
│   └── img3.jpg
├── Fungus Damage/
├── Green/
└── Insect Damage/</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Results Section ===== --}}
        @if(isset($batchResult))
        @php
            $br          = $batchResult;
            $classNames  = $br['class_names'];
            $cm          = $br['confusion_matrix'];   // 5×5
            $metrics     = $br['metrics'];
            $results     = $br['results'];
            $accuracy    = $br['accuracy'];
            $total       = $br['total_images'];
            $correct     = $br['correct_predictions'];
            // Find max CM value for heatmap scaling
            $maxCm = 1;
            foreach ($cm as $row) {
                foreach ($row as $val) {
                    if ($val > $maxCm) $maxCm = $val;
                }
            }
        @endphp

        {{-- ── Metric Summary Cards ── --}}
        <div class="row justify-content-center mb-4 g-3">
            <div class="col-6 col-md-3">
                <div class="metric-card" style="background:#fff8e1;">
                    <div class="metric-value text-warning">{{ $accuracy }}%</div>
                    <div class="metric-label"><i class="bi bi-bullseye"></i> Overall Accuracy</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="background:#e8f5e9;">
                    <div class="metric-value text-success">{{ $correct }}</div>
                    <div class="metric-label"><i class="bi bi-check-circle-fill"></i> Benar</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="background:#ffebee;">
                    <div class="metric-value text-danger">{{ $total - $correct }}</div>
                    <div class="metric-label"><i class="bi bi-x-circle-fill"></i> Salah</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card" style="background:#e3f2fd;">
                    <div class="metric-value text-primary">{{ $total }}</div>
                    <div class="metric-label"><i class="bi bi-images"></i> Total Gambar</div>
                </div>
            </div>
        </div>

        {{-- ── Confusion Matrix ── --}}
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-grid-3x3-gap-fill text-danger"></i> Confusion Matrix
                    </h5>
                    <p class="text-muted small mb-3">
                        Baris = Label Asli &nbsp;|&nbsp; Kolom = Prediksi Model.
                        Diagonal = prediksi benar; Warna lebih gelap = nilai lebih tinggi.
                    </p>
                    <div class="cm-wrap">
                        <table class="cm-table">
                            <thead>
                                <tr>
                                    <th style="background:transparent;color:transparent;min-width:110px;">—</th>
                                    @foreach($classNames as $col)
                                        <th title="{{ $col }}">{{ Str::limit($col, 10) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classNames as $ri => $rowClass)
                                <tr>
                                    <td class="row-label">{{ $rowClass }}</td>
                                    @foreach($classNames as $ci => $colClass)
                                    @php
                                        $val = $cm[$ri][$ci];
                                        $isDiag = ($ri === $ci);
                                        // Heatmap: intensity 0-1
                                        $intensity = $maxCm > 0 ? $val / $maxCm : 0;
                                        if ($isDiag) {
                                            // Green scale
                                            $r = (int)(34 + (1-$intensity)*180);
                                            $g = (int)(139 - (1-$intensity)*30);
                                            $b = (int)(34 + (1-$intensity)*120);
                                            $textC = $val > 0 ? '#fff' : '#999';
                                        } else {
                                            // Red scale
                                            $r = (int)(220 - (1-$intensity)*30);
                                            $g = (int)(53 - (1-$intensity)*20) + (int)((1-$intensity)*200);
                                            $b = (int)(69 - (1-$intensity)*30) + (int)((1-$intensity)*200);
                                            $textC = $val > 0 ? '#fff' : '#bbb';
                                        }
                                        $bg = "rgb($r,$g,$b)";
                                    @endphp
                                    <td class="cm-cell"
                                        style="background:{{ $bg }}; color:{{ $textC }};"
                                        title="{{ $rowClass }} → {{ $colClass }}: {{ $val }}">
                                        {{ $val }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Legend --}}
                    <div class="d-flex gap-3 mt-3 flex-wrap">
                        <span class="badge" style="background:#228b22; font-size:.8rem; padding:6px 12px;">
                            <i class="bi bi-square-fill"></i> Prediksi Benar (Diagonal)
                        </span>
                        <span class="badge" style="background:#dc3545; font-size:.8rem; padding:6px 12px;">
                            <i class="bi bi-square-fill"></i> Prediksi Salah
                        </span>
                        <span class="text-muted small align-self-center">Warna lebih gelap = nilai lebih besar</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Per-Class Metrics Table ── --}}
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-bar-chart-line-fill text-primary"></i> Metrik Per Kelas
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover cls-metrics-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th class="text-center">Precision (%)</th>
                                    <th class="text-center">Recall (%)</th>
                                    <th class="text-center">F1-Score (%)</th>
                                    <th class="text-center">Support</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics as $cls => $m)
                                <tr>
                                    <td class="fw-semibold">{{ $cls }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill"
                                              style="background:hsl({{ (int)($m['precision']*1.2) }},60%,42%); font-size:.85rem;">
                                            {{ $m['precision'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill"
                                              style="background:hsl({{ (int)($m['recall']*1.2) }},55%,40%); font-size:.85rem;">
                                            {{ $m['recall'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill"
                                              style="background:hsl({{ (int)($m['f1_score']*1.2) }},58%,38%); font-size:.85rem;">
                                            {{ $m['f1_score'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center text-muted">{{ $m['support'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-warning fw-bold">
                                    <td>Overall Accuracy</td>
                                    <td colspan="4" class="text-center">{{ $accuracy }}%
                                        ({{ $correct }} / {{ $total }} gambar benar)</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Per-Image Results Table ── --}}
        <div class="row justify-content-center mb-5" id="print-report">
            <div class="col-lg-10">
                <div class="glass-card p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3" >
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-table text-success"></i> Laporan Prediksi Per Gambar
                        </h5>
                        <span class="badge bg-secondary">{{ $total }} gambar</span>
                    </div>

                    {{-- Filter bar --}}
                    <div class="row g-2 mb-3" id="filterRow">
                        <div class="col-sm-4">
                            <input type="text" id="searchInput" class="form-control"
                                   placeholder="Cari nama file ...">
                        </div>
                        <div class="col-sm-4">
                            <select id="filterClass" class="form-select">
                                <option value="">Semua Kelas (True Label)</option>
                                @foreach($classNames as $cn)
                                    <option value="{{ $cn }}">{{ $cn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <select id="filterResult" class="form-select">
                                <option value="">Semua Hasil</option>
                                <option value="benar">✅ Benar</option>
                                <option value="salah">❌ Salah</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:480px; overflow-y:auto;">
                        <table class="table table-bordered table-hover results-table align-middle mb-0">
                            <thead class="sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Nama File</th>
                                    <th>Label Asli</th>
                                    <th>Prediksi</th>
                                    <th class="text-center">Confidence</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                @foreach($results as $idx => $r)
                                <tr class="result-row"
                                    data-true="{{ $r['true_label'] }}"
                                    data-correct="{{ $r['correct'] ? 'benar' : 'salah' }}">
                                    <td class="text-muted small">{{ $idx + 1 }}</td>
                                    <td class="fw-semibold" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                        title="{{ $r['filename'] }}">{{ $r['filename'] }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $r['true_label'] }}</span>
                                    </td>
                                    <td>
                                        @if($r['predicted_label'] === 'Error')
                                            <span class="badge bg-dark">Error</span>
                                        @else
                                            <span class="badge {{ $r['correct'] ? 'badge-correct' : 'badge-incorrect' }}">
                                                {{ $r['predicted_label'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(isset($r['confidence']))
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="progress flex-grow-1" style="height:10px;">
                                                <div class="progress-bar {{ $r['correct'] ? 'bg-success' : 'bg-danger' }}"
                                                     style="width:{{ $r['confidence'] }}%"></div>
                                            </div>
                                            <small class="fw-bold" style="min-width:42px;">{{ $r['confidence'] }}%</small>
                                        </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center fs-5">
                                        {{ $r['correct'] ? '✅' : '❌' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-2 small text-muted" id="showingCount"></div>
                </div>
            </div>
        </div>

        {{-- Print Header (hanya muncul saat cetak) --}}
        <div class="print-header">
            <img src="{{ asset('Image/LogoOval.png') }}" alt="BeanScope Logo" style="height:55px;">
            <h2 class="mt-2 mb-1">BeanScope &mdash; Laporan Batch Testing</h2>
            <p class="mb-0">Akurasi: <strong>{{ $accuracy }}%</strong> &nbsp;|&nbsp; Total: <strong>{{ $total }} gambar</strong> &nbsp;|&nbsp; Benar: <strong>{{ $correct }}</strong></p>
            <small>Tanggal cetak: {{ now()->format('d/m/Y H:i') }} WIB</small>
        </div>

        {{-- Action buttons --}}
        <div class="text-center mb-5 no-print" id="print-only-results">
            <button onclick="window.print()" class="btn btn-success btn-lg px-5 me-3">
                <i class="bi bi-printer-fill"></i> Cetak / Print Laporan
            </button>
            <a href="{{ route('batch-test.index') }}" class="btn btn-outline-light btn-lg px-5">
                <i class="bi bi-arrow-clockwise"></i> Upload ZIP Baru
            </a>
        </div>

        {{-- Print Footer --}}
        <div class="print-footer">
            Dicetak dari BeanScope &mdash; Sistem Deteksi Cacat Biji Kopi Berbasis AI
        </div>

        @endif {{-- end isset($batchResult) --}}

    </div>{{-- /container --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite('resources/js/script.js')
</body>
</html>

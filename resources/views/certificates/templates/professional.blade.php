{{-- resources/views/certificates/templates/professional.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificat - {{ $certificate->certificate_number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        @font-face {
            font-family: 'GreatVibes';
            src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .certificate-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: white;
            border: 15px solid #1e40af;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .border-pattern {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #f59e0b;
            pointer-events: none;
            z-index: 1;
        }

        .corner-decoration {
            position: absolute;
            width: 100px;
            height: 100px;
            z-index: 2;
        }

        .corner-tl {
            top: 15px;
            left: 15px;
            border-top: 3px solid #0f766e;
            border-left: 3px solid #0f766e;
        }

        .corner-tr {
            top: 15px;
            right: 15px;
            border-top: 3px solid #0f766e;
            border-right: 3px solid #0f766e;
        }

        .corner-bl {
            bottom: 15px;
            left: 15px;
            border-bottom: 3px solid #0f766e;
            border-left: 3px solid #0f766e;
        }

        .corner-br {
            bottom: 15px;
            right: 15px;
            border-bottom: 3px solid #0f766e;
            border-right: 3px solid #0f766e;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-family: 'GreatVibes', cursive;
            font-size: 150px;
            color: rgba(30, 64, 175, 0.05);
            white-space: nowrap;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 3;
            padding: 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .header {
            margin-bottom: 40px;
        }

        .logo {
            max-width: 120px;
            margin-bottom: 20px;
        }

        .institution-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 10px;
        }

        .institution-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .title {
            font-family: 'GreatVibes', cursive;
            font-size: 72px;
            color: #1e40af;
            margin-bottom: 20px;
            line-height: 1;
        }

        .subtitle {
            font-size: 18px;
            color: #4b5563;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .certificate-text {
            font-size: 16px;
            line-height: 1.8;
            color: #374151;
            margin-bottom: 40px;
            max-width: 700px;
        }

        .recipient-name {
            font-size: 48px;
            font-weight: bold;
            color: #0f766e;
            margin: 30px 0;
            padding: 20px 60px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 15px;
            border: 2px solid #bae6fd;
            display: inline-block;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .formation-details {
            background: #f8fafc;
            padding: 30px;
            border-radius: 10px;
            margin: 40px 0;
            max-width: 800px;
            border: 1px solid #e2e8f0;
        }

        .formation-title {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin: 15px 0;
        }

        .formation-meta {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
            font-size: 14px;
            color: #64748b;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-icon {
            color: #f59e0b;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 40px 0;
            width: 100%;
            max-width: 900px;
        }

        .detail-item {
            padding: 20px;
            background: #f1f5f9;
            border-radius: 8px;
            border-left: 4px solid #1e40af;
        }

        .detail-label {
            font-size: 12px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 2px solid #cbd5e1;
        }

        .signature-block {
            text-align: center;
            width: 250px;
        }

        .signature-image {
            height: 80px;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .signature-line {
            width: 200px;
            height: 1px;
            background: #000;
            margin: 20px auto 10px;
        }

        .signature-name {
            font-weight: bold;
            color: #1f2937;
            font-size: 16px;
        }

        .signature-title {
            font-size: 12px;
            color: #6b7280;
        }

        .certificate-number {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            font-family: monospace;
        }

        .qr-code {
            position: absolute;
            bottom: 20px;
            right: 30px;
            width: 80px;
            height: 80px;
        }

        .footer {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .security-stamp {
            position: absolute;
            top: 40px;
            right: 40px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(30,64,175,0.1) 0%, rgba(30,64,175,0) 70%);
            border: 2px dashed #1e40af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #1e40af;
            transform: rotate(15deg);
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Bordures décoratives -->
        <div class="border-pattern"></div>
        <div class="corner-decoration corner-tl"></div>
        <div class="corner-decoration corner-tr"></div>
        <div class="corner-decoration corner-bl"></div>
        <div class="corner-decoration corner-br"></div>

        <!-- Filigrane -->
        <div class="watermark">BCC-CENTER</div>

        <!-- Contenu principal -->
        <div class="content">
            <!-- En-tête -->
            <div class="header">
                <img src="{{ storage_path('app/public/logo.png') }}" class="logo" alt="BCC-Center">
                <div class="institution-name">BCC-Center</div>
                <div class="institution-subtitle">Centre de Formation Agréé • SIRET: 123 456 789 00012</div>
                <h1 class="title">Certificat de Formation</h1>
                <div class="subtitle">Accomplissement et Excellence</div>
            </div>

            <!-- Texte du certificat -->
            <div class="certificate-text">
                Le présent certificat est décerné à
            </div>

            <!-- Nom du participant -->
            <div class="recipient-name">
                {{ $user->name }}
            </div>

            <!-- Détails de la formation -->
            <div class="certificate-text">
                pour avoir suivi et réussi avec distinction la formation professionnelle
            </div>

            <div class="formation-details">
                <div class="formation-title">
                    {{ $formation->title }}
                </div>
                <div class="formation-meta">
                    <div class="meta-item">
                        <span class="meta-icon">⏱️</span>
                        <span>Durée: {{ $formation->duration }} heures</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">📅</span>
                        <span>Session: {{ $formation->sessions->first()->start_date->format('m/Y') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">🎯</span>
                        <span>Niveau: {{ ucfirst($formation->level) }}</span>
                    </div>
                </div>
            </div>

            <!-- Grille de détails -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Score Final</div>
                    <div class="detail-value">
                        @if($certificate->final_score)
                            {{ number_format($certificate->final_score, 1) }}/100
                        @else
                            Mention Très Bien
                        @endif
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Date d'émission</div>
                    <div class="detail-value">{{ $issueDate }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Validité</div>
                    <div class="detail-value">
                        @if($expiryDate)
                            Jusqu'au {{ $expiryDate }}
                        @else
                            Illimitée
                        @endif
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Numéro de Certificat</div>
                    <div class="detail-value">{{ $certificate->certificate_number }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Formateur</div>
                    <div class="detail-value">{{ $formateur->name ?? 'Expert Certifié' }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Heures de Formation</div>
                    <div class="detail-value">{{ $formation->duration }}h</div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-block">
                    <img src="{{ storage_path('app/public/signature-director.png') }}"
                         class="signature-image"
                         alt="Signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $director->name ?? 'Dr. Jean Martin' }}</div>
                    <div class="signature-title">Directeur Général, BCC-Center</div>
                </div>

                <div class="signature-block">
                    <img src="{{ storage_path('app/public/signature-formateur.png') }}"
                         class="signature-image"
                         alt="Signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $formateur->name ?? 'Prof. Marie Dubois' }}</div>
                    <div class="signature-title">Formateur Principal</div>
                </div>
            </div>

            <!-- Code QR pour vérification -->
            @if(file_exists(storage_path("app/{$certificate->qr_code_path}")))
            <img src="{{ storage_path("app/{$certificate->qr_code_path}") }}"
                 class="qr-code"
                 alt="QR Code de vérification">
            @endif

            <!-- Numéro de certificat -->
            <div class="certificate-number">
                Certificat No: {{ $certificate->certificate_number }} |
                Émis le: {{ $certificate->issue_date->format('d/m/Y') }} |
                Vérifier en ligne: https://bcc-center.com/certificats/verifier
            </div>
        </div>

        <!-- Cachet de sécurité -->
        <div class="security-stamp">
            OFFICIEL<br>
            BCC-CENTER<br>
            VERIFIÉ
        </div>

        <!-- Pied de page -->
        <div class="footer">
            Ce document officiel est généré électroniquement et ne nécessite pas de signature physique.<br>
            BCC-Center - 123 Avenue de la Formation, 75000 Paris - Tél: 01 23 45 67 89 - contact@bcc-center.com
        </div>
    </div>
</body>
</html>

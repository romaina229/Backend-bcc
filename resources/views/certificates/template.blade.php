{{-- resources/views/certificates/template.blade.php --}}
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

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #f9fafb;
        }

        .certificate-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: white;
            border: 20px solid #1e40af;
        }

        .watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ storage_path('app/templates/watermark.png') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 70%;
            opacity: 0.1;
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 30mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .header {
            margin-bottom: 20mm;
        }

        .logo {
            max-width: 150px;
            margin-bottom: 10mm;
        }

        .title {
            font-size: 48pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5mm;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .subtitle {
            font-size: 18pt;
            color: #4b5563;
            margin-bottom: 15mm;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 5mm;
            width: 100%;
        }

        .certificate-text {
            font-size: 14pt;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 20mm;
            max-width: 80%;
        }

        .recipient-name {
            font-size: 36pt;
            font-weight: bold;
            color: #0f766e;
            margin: 10mm 0;
            padding: 5mm 20mm;
            border: 3px solid #1e40af;
            border-radius: 10mm;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        .formation-details {
            font-size: 16pt;
            color: #1e40af;
            margin-bottom: 15mm;
        }

        .formation-title {
            font-size: 24pt;
            font-weight: bold;
            margin: 5mm 0;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10mm;
            margin-top: 15mm;
            width: 100%;
        }

        .detail-item {
            text-align: center;
            padding: 5mm;
            border-top: 1px solid #d1d5db;
        }

        .detail-label {
            font-size: 10pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2mm;
        }

        .detail-value {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 20mm;
            padding-top: 10mm;
            border-top: 2px solid #1e40af;
        }

        .signature-block {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            width: 150px;
            height: 1px;
            background: #000;
            margin: 10mm auto 2mm;
        }

        .signature-name {
            font-weight: bold;
            color: #1f2937;
        }

        .signature-title {
            font-size: 10pt;
            color: #6b7280;
        }

        .certificate-number {
            position: absolute;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
            color: #6b7280;
        }

        .qr-code {
            position: absolute;
            bottom: 10mm;
            right: 10mm;
            width: 40mm;
            height: 40mm;
        }

        .footer {
            position: absolute;
            bottom: 5mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Filigrane -->
        <div class="watermark"></div>

        <!-- Contenu principal -->
        <div class="content">
            <!-- Logo et en-tête -->
            <div class="header">
                <img src="{{ storage_path('app/public/logo.png') }}" class="logo" alt="BCC-Center">
                <h1 class="title">Certificat de Formation</h1>
                <div class="subtitle">Décerné avec succès</div>
            </div>

            <!-- Texte du certificat -->
            <div class="certificate-text">
                Ce certificat est décerné à
            </div>

            <!-- Nom du participant -->
            <div class="recipient-name">
                {{ $user->name }}
            </div>

            <!-- Détails de la formation -->
            <div class="formation-details">
                pour avoir complété avec succès la formation
                <div class="formation-title">
                    {{ $formation->title }}
                </div>
                d'une durée de {{ $formation->duration }} heures
            </div>

            <!-- Grille de détails -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Score Final</div>
                    <div class="detail-value">
                        @if($certificate->final_score)
                            {{ number_format($certificate->final_score, 1) }}/100
                        @else
                            N/A
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
                    <div class="detail-label">Niveau</div>
                    <div class="detail-value">
                        @switch($formation->level)
                            @case('debutant')
                                Débutant
                                @break
                            @case('intermediaire')
                                Intermédiaire
                                @break
                            @case('avance')
                                Avancé
                                @break
                        @endswitch
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $director->name ?? 'Nom du Directeur' }}</div>
                    <div class="signature-title">Directeur, BCC-Center</div>
                </div>

                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $formateur->name ?? 'Nom du Formateur' }}</div>
                    <div class="signature-title">Formateur Certifié</div>
                </div>
            </div>

            <!-- Code QR pour vérification -->
            @if($qrCode = app('App\Services\CertificateService')->generateQRCode($certificate))
            <img src="{{ $qrCode }}" class="qr-code" alt="QR Code de vérification">
            @endif

            <!-- Numéro de certificat -->
            <div class="certificate-number">
                Numéro de certificat: {{ $certificate->certificate_number }}
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            Ce certificat peut être vérifié en ligne à: https://bcc-center.com/certificat/verifier
            <br>
            BCC-Center - Centre de Formation Agréé • SIRET: XXXXXXXX • Contact: contact@bcc-center.com
        </div>
    </div>
</body>
</html>

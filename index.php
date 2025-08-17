<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kotcoin Bank - Welcome</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:700,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #111;
            color: #fff;
            font-family: 'Roboto', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }
        .bank-name {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 2.5rem;
            color: #fff;
            text-shadow: 0 4px 32px #000, 0 1px 0 #444;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            margin-bottom: 2.5rem;
        }
        .card {
            background: linear-gradient(135deg, #232526 0%, #414345 100%);
            color: #eee;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            padding: 2rem 1.5rem;
            min-width: 260px;
            max-width: 320px;
            flex: 1 1 260px;
            opacity: 0;
            transform: translateY(40px) scale(0.95);
            transition: box-shadow 0.2s;
        }
        .card:hover {
            box-shadow: 0 12px 48px rgba(0,0,0,0.7);
        }
        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.7rem;
        }
        .card-desc {
            color: #bbb;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .cta-btn {
            background: #ffb300;
            color: #222;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: background 0.2s, color 0.2s;
        }
        .cta-btn:hover {
            background: #ffd54f;
            color: #111;
        }
        @media (max-width: 900px) {
            .cards {
                flex-direction: column;
                gap: 1.5rem;
            }
        }
        @media (max-width: 600px) {
            .bank-name {
                font-size: 2rem;
            }
            .card {
                padding: 1.2rem 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="bank-name" id="bankName">Kotcoin Bank</div>
        <div class="cards">
            <div class="card" id="card1">
                <div class="card-title">Instant Payments</div>
                <div class="card-desc">Send and receive money instantly, securely, and with zero hassle. Your funds move at the speed of light.</div>
            </div>
            <div class="card" id="card2">
                <div class="card-title">API Integrations</div>
                <div class="card-desc">Integrate with our powerful APIs to automate your payment flows and track transactions in real time.</div>
            </div>
            <div class="card" id="card3">
                <div class="card-title">Live Payment Tracking</div>
                <div class="card-desc">Monitor your payments as they happen. Get notified the moment your money arrives.</div>
            </div>
            <div class="card" id="card4">
                <div class="card-title">Custom Payment Links</div>
                <div class="card-desc">Create and share payment links for easy, one-click payments. Perfect for businesses and individuals alike.</div>
            </div>
        </div>
        <a href="dashboard.php"><button class="cta-btn" id="ctaBtn">Go to Dashboard</button></a>
    </div>
    <script>
        // GSAP Animations
        window.onload = function() {
            gsap.fromTo("#bankName", {y: -60, opacity: 0, scale: 0.8}, {y: 0, opacity: 1, scale: 1, duration: 1, ease: "bounce.out"});
            gsap.to(".card", {
                opacity: 1,
                y: 0,
                scale: 1,
                stagger: 0.18,
                duration: 0.9,
                ease: "power3.out",
                delay: 0.5
            });
            gsap.fromTo("#ctaBtn", {scale: 0.7, opacity: 0}, {scale: 1, opacity: 1, duration: 0.7, delay: 1.2, ease: "elastic.out(1, 0.5)"});
        };
    </script>
</body>
</html> 
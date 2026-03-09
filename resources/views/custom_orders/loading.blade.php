<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Custom Order — Yakan</title>
    <script>
        // Redirect immediately — the loading screen shows during navigation
        window.location.href = "{{ $redirectUrl }}";
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #800000 0%, #500000 60%, #300000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .loading-container {
            text-align: center;
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .logo-icon {
            width: 52px;
            height: 52px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 900;
            color: #800000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .spinner-ring {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 30px;
        }
        .spinner-ring div {
            box-sizing: border-box;
            display: block;
            position: absolute;
            width: 64px;
            height: 64px;
            margin: 8px;
            border: 6px solid rgba(255,255,255,0.9);
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            border-color: rgba(255,255,255,0.9) transparent transparent transparent;
        }
        .spinner-ring div:nth-child(1) { animation-delay: -0.45s; }
        .spinner-ring div:nth-child(2) { animation-delay: -0.3s; }
        .spinner-ring div:nth-child(3) { animation-delay: -0.15s; }
        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            opacity: 0.95;
        }
        .loading-subtitle {
            font-size: 0.95rem;
            opacity: 0.7;
            letter-spacing: 0.5px;
        }
        .dots {
            display: inline-block;
            animation: dots 1.4s infinite;
        }
        @keyframes dots {
            0%,20%  { content: '.'; }
            40%     { content: '..'; }
            60%,100%{ content: '...'; }
        }
        .dots::after {
            content: '';
            animation: dotsAnim 1.4s infinite steps(1, end);
        }
        @keyframes dotsAnim {
            0%  { content: ''; }
            33% { content: '.'; }
            66% { content: '..'; }
            100%{ content: '...'; }
        }
        .progress-bar {
            width: 200px;
            height: 3px;
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
            margin: 24px auto 0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 3px;
            animation: progress 1.5s ease-in-out infinite;
        }
        @keyframes progress {
            0%   { width: 0%; margin-left: 0; }
            50%  { width: 70%; margin-left: 0; }
            100% { width: 0%; margin-left: 100%; }
        }
        .manual-link {
            margin-top: 30px;
            font-size: 0.85rem;
            opacity: 0.6;
        }
        .manual-link a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="logo-wrapper">
            <div class="logo-icon">Y</div>
            <div class="logo-text">Yakan</div>
        </div>

        <div class="spinner-ring">
            <div></div><div></div><div></div><div></div>
        </div>

        <div class="loading-title">Preparing Your Custom Order</div>
        <div class="loading-subtitle">Setting up your design wizard<span class="dots"></span></div>

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <div class="manual-link">
            Not redirecting? <a href="{{ $redirectUrl }}">Click here</a>
        </div>
    </div>
</body>
</html>

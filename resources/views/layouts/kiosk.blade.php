@extends('layouts.app')
@php $hideNavbar = true; @endphp

@section('title', 'Live AI Scanner')

@section('content')
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: linear-gradient(90deg, #5bb6c6, #6bb7e8) !important;
        }

        .viewport-center {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .white-card {
            background: white;
            width: 80%;
            max-width: 100vw;
            height: 90%;
            max-height: 100vh;
            border-radius: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4vh 20px;
            box-sizing: border-box;
        }

        .scanner-header h2 {
            font-size: clamp(1.8rem, 5vh, 2.8rem);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
            border-bottom: 5px solid #3b82f6;
            padding-bottom: 8px;
            text-align: center;
        }

        .video-relative {
            position: relative;
            width: 100%;
            max-width: 720px;
            aspect-ratio: 16 / 9;
            margin: 2vh 0;
            border-radius: 20px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        video#webcam,
        canvas#captureCanvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        #status {
            font-size: clamp(1.1rem, 2.5vh, 1.5rem);
            font-weight: 700;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }

        #locker-info {
            margin-top: 20px;
            width: 100%;
            animation: slideUp 0.5s ease-out;
        }

        #welcome-msg {
            font-size: 1.1rem;
            color: #1a1a1a;
            margin-bottom: 15px;
            font-weight: 500;
            text-align: center;
        }

        .locker-grid {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .locker-card {
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid #3b82f6;
            color: #1e40af;
            padding: 12px 25px;
            border-radius: 18px;
            font-size: 1.4rem;
            font-weight: 800;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
            transition: transform 0.2s;
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 3px;
            background: rgba(59, 130, 246, 0.8);
            box-shadow: 0 0 15px 5px rgba(59, 130, 246, 0.5);
            top: 0;
            z-index: 5;
            animation: scanMove 3.5s infinite ease-in-out;
        }

        @keyframes scanMove {

            0%,
            100% {
                top: 0%;
            }

            50% {
                top: 100%;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        canvas#captureCanvas {
            display: none;
        }
    </style>

    <div class="viewport-center">
        <div class="white-card">
            <div class="scanner-header">
                <h2>Live AI Scanner</h2>
            </div>

            <div class="video-relative">
                <div id="scan-line" class="scan-line"></div>
                <video id="webcam" autoplay muted playsinline></video>
                <canvas id="captureCanvas"></canvas>
            </div>

            <div id="status">Connecting to AI Server...</div>

            <div id="locker-info" style="display: none;">
                <p id="welcome-msg"></p>
                <div id="locker-list" class="locker-grid"></div>
                <div id="countdown-wrapper" style="margin-top: 25px; display: none; text-align: center;">
                    <p style="color: #666; font-size: 1.1rem;">
                        System will reset in <span id="timer" style="font-weight: bold; color: #3b82f6;">20</span>s
                    </p>
                    <button onclick="location.reload()"
                        style="background: #3b82f6; color: white; border: none; padding: 8px 20px; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 600; margin-top: 10px;">
                        Reset Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('captureCanvas');
        const scanLine = document.getElementById('scan-line');
        const status = document.getElementById('status');
        const lockerInfo = document.getElementById('locker-info');
        const lockerList = document.getElementById('locker-list');
        const welcomeMsg = document.getElementById('welcome-msg');
        const countdownWrapper = document.getElementById('countdown-wrapper');
        const timerDisplay = document.getElementById('timer');

        let isProcessingSuccess = false;
        let isAnalyzing = false;

        async function initScanner() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 1920
                        },
                        height: {
                            ideal: 1080
                        },
                        facingMode: "user"
                    }
                });
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    captureAndScan();
                };
            } catch (err) {
                status.innerText = "Camera Access Denied";
                status.style.color = "#dc3545";
            }
        }

        async function captureAndScan() {
            if (isProcessingSuccess) return;

            const ctx = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async (blob) => {
                if (!blob || isProcessingSuccess) return;
                const formData = new FormData();
                formData.append('images', blob, 'frame.jpg');

                try {
                    const response = await fetch("{{ route('qr.verify') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.length > 0) {
                            const res = data[0];

                            // 1. QR PATH: We already have the Locker ID, so we go straight to UI
                            if (res.type === "qr_success") {
                                handleSuccess(
                                    `QR Verified: Unit #${res.locker_id}`,
                                    [res.locker_id],
                                    "QR Code Accepted",
                                    res.user_id,
                                    "qr"
                                );
                            }
                            // 2. FACE PATH: We only know the person. Fetch lockers FIRST, then update UI.
                            else if (res.result !== 'STRANGER' && res.user_id) {
                                status.innerText = "Identity Verified. Fetching lockers...";
                                status.style.color = "#28a745";

                                // We do NOT call handleSuccess here anymore.
                                // We wait for the data in fetchActiveLockers.
                                fetchActiveLockers(res.user_id, res.result);
                            }
                            // 3. ERROR STATES
                            else if (res.type === "qr_used" || res.type === "qr_error") {
                                status.innerText = res.result;
                                status.style.color = "#dc3545";
                                if (res.type === "qr_used") {
                                    isProcessingSuccess = true;
                                    startRefreshCountdown();
                                }
                            }
                        }
                    }
                } catch (e) {
                    status.innerText = "AI Server Offline...";
                }

                if (!isProcessingSuccess) {
                    setTimeout(captureAndScan, 1000);
                }
            }, 'image/jpeg', 0.8);
        }

        /**
         * Fetches active locker sessions for a detected user
         */
        async function fetchActiveLockers(userId, name) {
            try {
                const response = await fetch(`/users/${userId}/active-lockers`);
                const lockers = await response.json();
                const lockerIds = lockers.map(l => l.locker_id);

                // Now that we have the name AND the locker IDs, call UI update ONCE.
                if (lockerIds.length > 0) {
                    handleSuccess(
                        `Welcome back, ${name}`,
                        lockerIds,
                        `Your active units are ready:`,
                        userId,
                        "face"
                    );
                } else {
                    handleSuccess(
                        `Welcome, ${name}`,
                        [],
                        `No locker have been assigned to you.`,
                        userId,
                        "face"
                    );
                }
            } catch (err) {
                console.error("Locker Fetch Error:", err);
                status.innerText = "Error retrieving locker data.";
            }
        }

        // Sends the list of locker IDs to the local hardware bridge
        async function sendToLockerController(lockerIds) {
            try {
                for (const lockerId of lockerIds) {
                    const message = `0123_${lockerId}`;

                    console.log("Sending command:", message);

                    const response = await fetch('http://localhost:2200/api/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: message // ⬅️ HARUS message
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hardware controller unreachable');
                    }
                }

                console.log("All unlock signals sent successfully");
            } catch (err) {
                console.error("Hardware Error:", err);
                status.innerText += " (Hardware Error)";
                status.style.color = "#dc3545";
            }
        }

        async function updateLockerStatusInDB(lockerIds, userId) {
            try {
                await fetch('/lockers/update-statuses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        locker_ids: lockerIds,
                        user_id: userId
                    })
                });
            } catch (e) {
                console.error("DB Update Failed:", e);
            }
        }

        /**
         * UI Handler for successful access: Stops camera, freezes frame, shows countdown
         */
        async function handleSuccess(statusText, lockerIds, welcomeText, userId, successType) {
            isProcessingSuccess = true;

            // 1. Freeze the last frame
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            video.style.display = 'none';
            canvas.style.display = 'block';
            scanLine.style.display = 'none';

            // 2. Stop the camera hardware
            if (video.srcObject) {
                const tracks = video.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                video.srcObject = null;
            }

            // 3. Update UI
            status.innerText = statusText;
            status.style.color = "#28a745";
            welcomeMsg.innerText = welcomeText;
            lockerInfo.style.display = 'block';

            if (lockerIds.length > 0) {
                lockerList.innerHTML = lockerIds.map(id => `
            <div class="locker-card">
                <span class="locker-label">Unit</span>
                #${id}
            </div>
        `).join('');

                // --- NEW: Send to Local Locker Hardware Controller ---
                sendToLockerController(lockerIds);
                if (userId && successType == 'face') {
                    updateLockerStatusInDB(lockerIds, userId, );
                }
            } else {
                lockerList.innerHTML = '';
            }

            startRefreshCountdown();
        }

        /**
         * 20s Countdown before refreshing
         */
        function startRefreshCountdown() {
            let timeLeft = 20;
            countdownWrapper.style.display = 'block';

            const interval = setInterval(() => {
                timeLeft--;
                timerDisplay.innerText = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    location.reload();
                }
            }, 1000);
        }

        initScanner();
    </script>
@endsection

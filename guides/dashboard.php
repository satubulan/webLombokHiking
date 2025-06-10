<?php
session_start();
require_once '../config.php';

// Redirect kalau belum login atau bukan guide
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$guideId = $_SESSION['user_id'];
$guideName = $_SESSION['user_name'];

// Ambil statistik trip dan booking
// Total trip yang dipandu
$stmtTrip = $conn->prepare("SELECT COUNT(*) AS total_trip FROM trips WHERE guide_id = ?");
$stmtTrip->bind_param("i", $guideId);
$stmtTrip->execute();
$tripResult = $stmtTrip->get_result()->fetch_assoc();
$totalTrip = $tripResult['total_trip'];
// Total peserta (booking)
$stmtPeserta = $conn->prepare("SELECT COUNT(*) AS total_peserta FROM bookings b JOIN trips t ON b.trip_id = t.id WHERE t.guide_id = ?");
$stmtPeserta->bind_param("i", $guideId);
$stmtPeserta->execute();
$pesertaResult = $stmtPeserta->get_result()->fetch_assoc();
$totalPeserta = $pesertaResult['total_peserta'];

// Total pendapatan (simulasi dari harga trip * jumlah booking confirmed)
$stmtIncome = $conn->prepare("SELECT SUM(t.price) AS total_income FROM bookings b JOIN trips t ON b.trip_id = t.id WHERE t.guide_id = ? AND b.status = 'confirmed'");
$stmtIncome->bind_param("i", $guideId);
$stmtIncome->execute();
$incomeResult = $stmtIncome->get_result()->fetch_assoc();
$totalIncome = $incomeResult['total_income'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guide - Lombok Hiking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg,rgb(67, 145, 115),rgb(40, 154, 92) 100%);
            min-height: 100vh;
            color: #2d3748;
        }
        
        .hero-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 500"><path d="M0,300 Q250,200 500,300 T1000,300 L1000,500 L0,500 Z" fill="%23ffffff10"/><path d="M0,350 Q250,250 500,350 T1000,350 L1000,500 L0,500 Z" fill="%23ffffff05"/></svg>') no-repeat bottom;
            background-size: cover;
            z-index: -1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.8s ease-out;
        }
        
        .welcome-text {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .mountain-icon {
            font-size: 2.5rem;
            animation: bounce 2s infinite;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg,rgb(67, 145, 115),rgb(40, 154, 92));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header p {
            font-size: 1.1rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.8s ease-out;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.7s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        .stat-card h3 {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg,rgb(67, 145, 115),rgb(40, 154, 92));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            font-size: 0.9rem;
            color: #10b981;
            font-weight: 600;
        }
        
        .actions-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.8s ease-out 0.4s both;
        }
        
        .actions-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2d3748;
            font-weight: 700;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            text-align: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #2d3748;
            border: 2px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f56565, #e53e3e);
            color: white;
            box-shadow: 0 8px 25px rgba(245, 101, 101, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-3px);
        }
        
        .btn-primary:hover {
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
        }
        
        .btn-secondary:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .btn-danger:hover {
            box-shadow: 0 15px 35px rgba(245, 101, 101, 0.6);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .flash-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            animation: slideUp 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="hero-bg"></div>
    
    <div class="container">
        <!-- Flash Message (if any) -->
        <!-- You can add PHP here to show flash messages -->
        
        <div class="header">
            <div class="welcome-text">
                <span class="mountain-icon">🏔️</span>
                <div>
                    <h1>Selamat datang, Guide <?php echo htmlspecialchars($guideName); ?>!</h1>
                    <p>Siap untuk petualangan hiking hari ini? Mari kelola trip Anda dengan mudah.</p>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🥾</div>
                <h3>Total Trip</h3>
                <div class="stat-value"><?= $totalTrip; ?></div>
                <div class="stat-change">+2 bulan ini</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <h3>Total Peserta</h3>
                <div class="stat-value"><?= $totalPeserta; ?></div>
                <div class="stat-change">+8 bulan ini</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <h3>Total Pendapatan</h3>
                <div class="stat-value"><?= number_format($totalIncome / 1000000, 1); ?>M</div>
                <div class="stat-change">+2.3M bulan ini</div>
            </div>
        </div>

        <div class="actions-section">
            <h2>🎯 Menu Utama</h2>
            <div class="actions-grid">
                <a href="manage-booking.php" class="btn btn-primary">
                    📋 Kelola Booking
                </a>
                <a href="profile.php" class="btn btn-secondary">
                    ✏️ Edit Profil
                </a>
                <a href="chat-room.php" class="btn btn-secondary">
                    💬 Chat Room
                </a>
                <a href="my-trips.php" class="btn btn-secondary">
                    🗺️ Trip Saya
                </a>
                <a href="Trip-stats-guide.php" class="btn btn-secondary">
                    📈 Statistik Trip
                </a>
                <a href="../logout.php" class="btn btn-danger">
                    🚪 Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate numbers on load
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                const finalValue = stat.textContent;
                let currentValue = 0;
                const increment = finalValue.includes('M') ? 0.1 : 1;
                const duration = 1500;
                const stepTime = duration / (parseFloat(finalValue) || 1);
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (finalValue.includes('M') && currentValue >= parseFloat(finalValue)) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else if (!finalValue.includes('M') && currentValue >= parseInt(finalValue)) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else {
                        stat.textContent = finalValue.includes('M') ? 
                            currentValue.toFixed(1) + 'M' : 
                            Math.floor(currentValue);
                    }
                }, stepTime);
            });
            
            // Add ripple effect to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.6);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        left: ${x}px;
                        top: ${y}px;
                        width: ${size}px;
                        height: ${size}px;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });
        });
        
        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
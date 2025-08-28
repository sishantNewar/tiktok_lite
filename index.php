<?php
// Start session for user authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require "db.php";

// Display any messages from other pages
if (isset($_SESSION['error'])) {
    echo '<div class="notification error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['message'])) {
    echo '<div class="notification success">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Check if videos table exists and get videos
$videos = [];
$table_exists = $mysqli->query("SHOW TABLES LIKE 'videos'");
if ($table_exists && $table_exists->num_rows > 0) {
    // Modified query to handle cases where users might be deleted
    $result = $mysqli->query("
        SELECT v.*, COALESCE(u.username, 'deleted_user') as username 
        FROM videos v 
        LEFT JOIN users u ON v.user_id = u.id 
        ORDER BY v.created_at DESC
    ");
    if ($result && $result->num_rows > 0) {
        $videos = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Clone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }
        
        body {
            background-color: #000;
            color: #fff;
            overflow-x: hidden;
        }
        
        .container {
            display: flex;
            max-width: 100%;
            margin: 0 auto;
            position: relative;
            height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 220px;
            background-color: #121212;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid #2f2f2f;
            z-index: 100;
        }
        
        .logo {
            font-weight: bold;
            font-size: 1.8rem;
            margin-bottom: 30px;
            background: linear-gradient(45deg, #FE2C55, #25F4EE);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }
        
        .sidebar nav ul {
            list-style: none;
        }
        
        .sidebar nav ul li {
            padding: 15px 10px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }
        
        .sidebar nav ul li:hover {
            background-color: #2a2a2a;
        }
        
        .sidebar nav ul li.active {
            background-color: #2a2a2a;
            color: #FE2C55;
        }
        
        .auth-buttons {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .auth-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
            border: none;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        
        .login-btn {
            background: linear-gradient(45deg, #FE2C55, #25F4EE);
            color: white;
        }
        
        .register-btn {
            background: transparent;
            color: #25F4EE;
            border: 1px solid #25F4EE;
        }
        
        .auth-btn:hover {
            transform: scale(1.03);
        }
        
        .user-info {
            margin-top: auto;
            padding: 15px;
            background-color: #2a2a2a;
            border-radius: 8px;
            text-align: center;
        }
        
        .user-info p {
            margin-bottom: 10px;
        }
        
        .logout-btn {
            background: #FE2C55;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        
        /* Video Feed Styles */
        .video-feed {
            margin-left: 220px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            scroll-snap-type: y mandatory;
            height: 100vh;
        }
        
        .video-card {
            position: relative;
            height: 100vh;
            scroll-snap-align: start;
            border-bottom: 1px solid #2f2f2f;
        }
        
        .video-player {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: linear-gradient(45deg, #0f0f0f, #252525);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .video-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
        }
        
        .video-info h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .video-info p {
            color: #ddd;
            margin-bottom: 8px;
            max-width: 70%;
        }
        
        .hashtags {
            color: #25F4EE;
            font-weight: 500;
        }
        
        .actions {
            position: absolute;
            right: 20px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #FE2C55, #25F4EE);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 2px solid white;
        }
        
        .action {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }
        
        .action i {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .action span {
            font-size: 0.9rem;
        }
        
        /* Upload Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #25F4EE;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #2f2f2f;
            border-radius: 8px;
            background: #121212;
            color: white;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .submit-btn {
            background: linear-gradient(45deg, #FE2C55, #25F4EE);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
        }
        
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 1100;
            display: none;
        }
        
        .notification.success {
            background: #00C851;
            color: white;
        }
        
        .notification.error {
            background: #ff4444;
            color: white;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 15px 10px;
            }
            
            .sidebar nav ul li {
                text-align: center;
                font-size: 0.9rem;
                padding: 12px 5px;
            }
            
            .sidebar nav ul li span {
                display: none;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .video-feed {
                margin-left: 80px;
            }
            
            .auth-btn span {
                display: none;
            }
            
            .user-info {
                padding: 10px 5px;
            }
            
            .user-info p {
                font-size: 0.8rem;
            }
            
            .video-info p {
                max-width: 60%;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        /* Auth Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content-auth {
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            position: relative;
        }

        .close-button {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .tab-container {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #2f2f2f;
        }

        .tab-button {
            background: none;
            border: none;
            color: #ddd;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1rem;
        }

        .tab-button.active {
            color: #25F4EE;
            border-bottom: 2px solid #25F4EE;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        .auth-form h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #25F4EE;
        }

        .auth-form .form-group {
            margin-bottom: 15px;
        }

        .auth-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .auth-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #2f2f2f;
            border-radius: 8px;
            background: #121212;
            color: white;
        }

        .form-switch {
            text-align: center;
            margin-top: 15px;
        }

        .form-switch a {
            color: #25F4EE;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h1 class="logo">TikTok</h1>
            <nav>
                <ul>
                    <li class="active"><i class="fas fa-home"></i> <span>For You</span></li>
                    <li><i class="fas fa-search"></i> <span>Explore</span></li>
                    <li><i class="fas fa-user-friends"></i> <span>Following</span></li>
                    <li id="upload-btn"><i class="fas fa-cloud-upload-alt"></i> <span>Upload</span></li>
                    <li><i class="fas fa-broadcast-tower"></i> <span>Live</span></li>
                    <li><i class="fas fa-user"></i> <span>Profile</span></li>
                    <li><i class="fas fa-ellipsis-h"></i> <span>More</span></li>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <?php if ($isLoggedIn): ?>
                    <div class="user-info">
                        <p>Welcome, <?php echo $_SESSION['username']; ?></p>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="#" class="auth-btn login-btn" id="login-modal-btn"><span>Log In</span></a>
                    <a href="#" class="auth-btn register-btn" id="register-modal-btn"><span>Register</span></a>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Feed -->
        <main class="video-feed">
            <?php if (!empty($videos)): ?>
                <?php foreach ($videos as $video): ?>
                    <section class="video-card fade-in">
                        <div class="video-player">
    <video width="100%" height="100%" controls autoplay muted loop>
        <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>



                        <div class="video-content">
                            <div class="video-info">
                                <h3>@<?php echo htmlspecialchars($video['username']); ?></h3>
                                <p><?php echo htmlspecialchars($video['title']); ?></p>
                                <p class="hashtags">#<?php echo htmlspecialchars($video['description']); ?></p>
                            </div>
                        </div>
                        <div class="actions">
                            <div class="profile-pic"><i class="fas fa-user"></i></div>
                            <div class="action">
                                <i class="fas fa-heart"></i>
                                <span><?php echo number_format($video['likes']); ?></span>
                            </div>
                            <div class="action">
                                <i class="fas fa-comment"></i>
                                <span><?php echo number_format($video['comments']); ?></span>
                            </div>
                            <div class="action">
                                <i class="fas fa-bookmark"></i>
                                <span><?php echo number_format($video['shares']); ?></span>
                            </div>
                            <div class="action">
                                <i class="fas fa-share"></i>
                                <span><?php echo number_format($video['shares']); ?></span>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Hardcoded videos as fallback -->
                <section class="video-card fade-in">
                    <div class="video-player">
                        <i class="fas fa-play-circle" style="font-size: 3rem;"></i>
                    </div>
                    <div class="video-content">
                        <div class="video-info">
                            <h3>@creativeuser</h3>
                            <p>Check out this amazing trick shot! 🏀</p>
                            <p class="hashtags">#basketball #trickshot #viral</p>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="profile-pic"><i class="fas fa-user"></i></div>
                        <div class="action">
                            <i class="fas fa-heart"></i>
                            <span>151.5K</span>
                        </div>
                        <div class="action">
                            <i class="fas fa-comment"></i>
                            <span>2,468</span>
                        </div>
                        <div class="action">
                            <i class="fas fa-bookmark"></i>
                            <span>17.7K</span>
                        </div>
                        <div class="action">
                            <i class="fas fa-share"></i>
                            <span>18.6K</span>
                        </div>
                    </div>
                </section>


                
            <?php endif; ?>
        </main>
    </div>

    <!-- Upload Modal -->
    <div class="modal" id="upload-modal">
        <div class="modal-content">
            <button class="close-btn" id="close-upload">&times;</button>
            <h2 class="modal-title">Upload a Video</h2>
            <form id="upload-form" action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Video Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="video">Video File *</label>
                    <input type="file" id="video" name="video" accept="video/*" required>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="age_restriction" name="age_restriction" value="1">
                        <label for="age_restriction">Age Restricted (18+)</label>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Upload Video</button>
            </form>
        </div>
    </div>

    <!-- Login/Signup Modal -->
    <div id="authModal" class="modal-overlay">
        <div class="modal-content-auth">
            <span class="close-button" id="closeModalButton">&times;</span>
            <div class="tab-container">
                <button class="tab-button active" id="loginTab">Log In</button>
                <button class="tab-button" id="signupTab">Sign Up</button>
            </div>

            <div id="loginFormContainer" class="form-container active">
                <form class="auth-form" action="process_auth.php" method="post">
                    <h2>Log In</h2>
                    <div class="form-group">
                        <label for="loginUsername">Username or Email</label>
                        <input type="text" id="loginUsername" name="loginUsername" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="loginPassword" required>
                    </div>
                    <input type="hidden" name="action" value="login">
                    <button type="submit" class="submit-btn">Log In</button>
                    <p class="form-switch">Don't have an account? <a href="#" id="switchToSignup">Sign Up</a></p>
                </form>
            </div>

            <div id="signupFormContainer" class="form-container">
                <form class="auth-form" action="process_auth.php" method="post">
                    <h2>Sign Up</h2>
                    <div class="form-group">
                        <label for="signupUsername">Username</label>
                        <input type="text" id="signupUsername" name="signupUsername" required>
                    </div>
                    <div class="form-group">
                        <label for="signupEmail">Email</label>
                        <input type="email" id="signupEmail" name="signupEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="signupPassword">Password</label>
                        <input type="password" id="signupPassword" name="signupPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <input type="hidden" name="action" value="signup">
                    <button type="submit" class="submit-btn">Sign Up</button>
                    <p class="form-switch">Already have an account? <a href="#" id="switchToLogin">Log In</a></p>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const uploadBtn = document.getElementById('upload-btn');
            const uploadModal = document.getElementById('upload-modal');
            const closeUpload = document.getElementById('close-upload');
            
            const loginModalBtn = document.getElementById('login-modal-btn');
            const registerModalBtn = document.getElementById('register-modal-btn');
            
            const notification = document.getElementById('notification');
            
            // Auth modal functionality
            const authModal = document.getElementById('authModal');
            const closeModalButton = document.getElementById('closeModalButton');
            const loginTab = document.getElementById('loginTab');
            const signupTab = document.getElementById('signupTab');
            const loginFormContainer = document.getElementById('loginFormContainer');
            const signupFormContainer = document.getElementById('signupFormContainer');
            const switchToSignup = document.getElementById('switchToSignup');
            const switchToLogin = document.getElementById('switchToLogin');

            // Open modals
            uploadBtn.addEventListener('click', () => {
                uploadModal.style.display = 'flex';
            });
            
            loginModalBtn.addEventListener('click', (e) => {
                e.preventDefault();
                authModal.style.display = 'flex';
                showLoginForm();
            });
            
            registerModalBtn.addEventListener('click', (e) => {
                e.preventDefault();
                authModal.style.display = 'flex';
                showSignupForm();
            });
            
            // Close modals
            closeUpload.addEventListener('click', () => {
                uploadModal.style.display = 'none';
            });
            
            closeModalButton.addEventListener('click', () => {
                authModal.style.display = 'none';
            });
            
            // Tab switching
            loginTab.addEventListener('click', showLoginForm);
            signupTab.addEventListener('click', showSignupForm);
            switchToSignup.addEventListener('click', (e) => {
                e.preventDefault();
                showSignupForm();
            });
            switchToLogin.addEventListener('click', (e) => {
                e.preventDefault();
                showLoginForm();
            });

            function showLoginForm() {
                loginTab.classList.add('active');
                signupTab.classList.remove('active');
                loginFormContainer.classList.add('active');
                signupFormContainer.classList.remove('active');
            }

            function showSignupForm() {
                signupTab.classList.add('active');
                loginTab.classList.remove('active');
                signupFormContainer.classList.add('active');
                loginFormContainer.classList.remove('active');
            }
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === uploadModal) {
                    uploadModal.style.display = 'none';
                }
                if (e.target === authModal) {
                    authModal.style.display = 'none';
                }
            });
            
            // Like functionality
            const likeButtons = document.querySelectorAll('.fa-heart');
            likeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('fas');
                    this.classList.toggle('far');
                    
                    const countElement = this.parentElement.nextElementSibling;
                    let count = parseInt(countElement.textContent.replace(/,/g, ''));
                    
                    if (this.classList.contains('fas')) {
                        count++;
                        this.style.color = '#FE2C55';
                    } else {
                        count--;
                        this.style.color = '';
                    }
                    
                    countElement.textContent = count.toLocaleString();
                });
            });
            
            // Bookmark functionality
            const bookmarkButtons = document.querySelectorAll('.fa-bookmark');
            bookmarkButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('fas');
                    this.classList.toggle('far');
                    
                    const countElement = this.parentElement.nextElementSibling;
                    let count = parseInt(countElement.textContent.replace(/,/g, ''));
                    
                    if (this.classList.contains('fas')) {
                        count++;
                    } else {
                        count--;
                    }
                    
                    countElement.textContent = count.toLocaleString();
                });
            });

            const uploadForm = document.getElementById('upload-form');
uploadForm.addEventListener('submit', function(e) {
    e.preventDefault(); // prevent default page reload

    const formData = new FormData(uploadForm);

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification('Video uploaded successfully!', 'success');

            // Optionally, reset form
            uploadForm.reset();

            // Optionally, close modal
            document.getElementById('upload-modal').style.display = 'none';

            // Optionally, reload the video feed to include the new video
            // location.reload();  <-- or do AJAX fetch to append new video dynamically
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showNotification('Upload failed. Try again.', 'error');
    });
});

            
            // Show notification function
            function showNotification(message, type) {
                notification.textContent = message;
                notification.className = 'notification ' + type;
                notification.style.display = 'block';
                
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
            
            // Simulate video loading
            const videoPlayers = document.querySelectorAll('.video-player');
            videoPlayers.forEach(player => {
                player.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon.style.display === 'none') {
                        icon.style.display = 'block';
                    } else {
                        icon.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
$pageTitle = 'Video Debug';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-bug"></i> Video Background Debug</h3>
                </div>
                <div class="card-body">
                    <h5>Video File Information:</h5>
                    <ul>
                        <li><strong>Video Path:</strong> <?= BASE_URL ?>assets/videos/background.mp4</li>
                        <li><strong>File Exists:</strong> <?= file_exists(APP_ROOT . '/public/assets/videos/background.mp4') ? 'Yes' : 'No' ?></li>
                        <li><strong>File Size:</strong> <?= file_exists(APP_ROOT . '/public/assets/videos/background.mp4') ? number_format(filesize(APP_ROOT . '/public/assets/videos/background.mp4') / 1024 / 1024, 2) . ' MB' : 'N/A' ?></li>
                        <li><strong>Base URL:</strong> <?= BASE_URL ?></li>
                        <li><strong>App Root:</strong> <?= APP_ROOT ?></li>
                    </ul>
                    
                    <h5>Browser Console:</h5>
                    <p>Open your browser's developer tools (F12) and check the Console tab for any video-related errors.</p>
                    
                    <h5>Test Video Element:</h5>
                    <video id="test-video" width="400" height="200" controls>
                        <source src="<?= BASE_URL ?>assets/videos/background.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="testVideo()">Test Video Playback</button>
                        <button class="btn btn-secondary" onclick="checkVideoStatus()">Check Video Status</button>
                    </div>
                    
                    <div id="video-status" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testVideo() {
    const video = document.getElementById('test-video');
    const status = document.getElementById('video-status');
    
    video.play().then(() => {
        status.innerHTML = '<div class="alert alert-success">Video plays successfully!</div>';
    }).catch((error) => {
        status.innerHTML = '<div class="alert alert-danger">Video play failed: ' + error.message + '</div>';
    });
}

function checkVideoStatus() {
    const video = document.getElementById('test-video');
    const status = document.getElementById('video-status');
    
    let info = '<div class="alert alert-info"><h6>Video Status:</h6><ul>';
    info += '<li>Ready State: ' + video.readyState + '</li>';
    info += '<li>Network State: ' + video.networkState + '</li>';
    info += '<li>Current Time: ' + video.currentTime + '</li>';
    info += '<li>Duration: ' + video.duration + '</li>';
    info += '<li>Paused: ' + video.paused + '</li>';
    info += '<li>Ended: ' + video.ended + '</li>';
    info += '<li>Error: ' + (video.error ? video.error.message : 'None') + '</li>';
    info += '</ul></div>';
    
    status.innerHTML = info;
}

// Check background video
document.addEventListener('DOMContentLoaded', function() {
    const bgVideo = document.getElementById('background-video');
    if (bgVideo) {
        console.log('Background video element found');
        console.log('Video src:', bgVideo.src || bgVideo.querySelector('source').src);
        
        bgVideo.addEventListener('loadstart', () => console.log('Video load started'));
        bgVideo.addEventListener('loadeddata', () => console.log('Video data loaded'));
        bgVideo.addEventListener('canplay', () => console.log('Video can play'));
        bgVideo.addEventListener('error', (e) => console.log('Video error:', e));
    } else {
        console.log('Background video element not found');
    }
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

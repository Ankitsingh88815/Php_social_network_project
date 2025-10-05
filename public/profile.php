 <?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Post.php';

$auth = new Auth();
$userId = $auth->check();
if (!$userId) {
    header('Location: index.php');
    exit;
}

$user = $auth->user();
$postModel = new Post();
$posts = $postModel->getRecentAll(50);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Page-specific modal, buttons, extra UI tweaks (all-in-one-place for easy override) */
    .btn { padding:8px 12px; border:none; border-radius:8px; cursor:pointer; }
    .btn-secondary { background:#457b9d; color:#fff; }
    .btn-secondary:hover { background:#1d3557; }
    .btn-danger { background:#e63946; color:#fff; }
    .btn-danger:hover { background:#d62828; }

    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; z-index:9999; }
    .modal { max-width:520px; margin:6vh auto; background:#fff; border-radius:12px; padding:16px; box-shadow:0 8px 24px rgba(0,0,0,.18); }
    .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
    .close-x { border:none; background:transparent; font-size:20px; cursor:pointer; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .grid-2 .full { grid-column:1 / -1; }
    .field label { display:block; font-size:13px; color:#555; margin:4px 2px; }
    .field input { width:100%; padding:10px 12px; border:1px solid #cfd6e4; border-radius:10px; background:#f8fafc; outline:0; }
    .field input:focus { border-color:#1971f0; background:#fff; }
    .avatar-ring { width:96px;height:96px;border-radius:50%;background:#eef;overflow:hidden;margin:0 auto 8px; }
    #profileUpdateMsg { text-align:center; font-weight:600; margin-top:8px; }
    @media (max-width:680px){ .grid-2 { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<div class="container">

  <aside class="profile-box">
    <div class="avatar">
      <?php if (!empty($user['profile_pic'])): ?>
        <img id="profileAvatar" src="<?= htmlspecialchars(UPLOAD_URL . $user['profile_pic']) ?>" alt="avatar" style="max-width:100px;border-radius:50%;">
      <?php else: ?>
        <div class="placeholder-avatar">?</div>
      <?php endif; ?>
    </div>
    <h3 id="profileName"><?= htmlspecialchars($user['fullname']) ?></h3>
    <p><?= htmlspecialchars($user['email']) ?></p>
    <p>Age: <span id="profileAge"><?= htmlspecialchars($user['age']) ?></span></p>
    <div style="display:flex; gap:8px; justify-content:center; margin-top:10px;">
      <button id="editProfileBtn" class="btn btn-secondary">Edit Profile</button>
      <button id="logoutBtn" class="btn btn-danger">Logout</button>
    </div>
  </aside>
  <!-- Main Feed -->
  <main class="feed">
    <section class="new-post card">
      <h4>Add Post</h4>
      <form id="postForm" enctype="multipart/form-data">
        <textarea id="postDesc" name="description" placeholder="What's on your mind?"></textarea>
        <!-- Image picker -->
        <div class="image-picker">
          <div id="imagePreviewBox" class="img-preview hidden">
            <img id="imagePreview" alt="selected image preview">
            <button type="button" id="removeImageBtn" class="img-remove" aria-label="Remove image">✕</button>
          </div>
          <input type="file" id="postImage" name="image" accept="image/*" hidden>
          <button type="button" id="addImageBtn" class="btn-text">
            <span style="display:inline-block;width:14px;height:14px;border:2px solid currentColor;border-radius:3px;line-height:12px;text-align:center;margin-right:6px;">+</span>
            Add Image
          </button>
        </div>
        <button type="submit">Post</button>
      </form>
      <div id="postMsg" style="color:green;margin-top:8px;"></div>
    </section>
    <section id="posts">
      <?php foreach ($posts as $p):
          $likes = isset($p['likes']) ? (int)$p['likes'] : 0;
          $dislikes = isset($p['dislikes']) ? (int)$p['dislikes'] : 0;
          $isOwner = ((int)$p['user_id'] === (int)$userId);
          $userAvatar = !empty($p['profile_pic']) ? UPLOAD_URL . $p['profile_pic'] : null;
          $postImage = !empty($p['image']) ? UPLOAD_URL . $p['image'] : null;
      ?>
      <article class="post card" data-post-id="<?= (int)$p['id'] ?>">
        <div class="post-header" style="display:flex;justify-content:space-between;align-items:center;">
          <div style="display:flex; align-items:center; gap:8px;">
            <?php if ($userAvatar): ?>
              <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" style="width:40px;height:40px;border-radius:50%;">
            <?php else: ?>
              <div style="width:40px;height:40px;border-radius:50%;background:#ddd;display:inline-block;"></div>
            <?php endif; ?>
            <div>
              <strong><?= htmlspecialchars($p['fullname']) ?></strong><br>
              <small><?= htmlspecialchars($p['created_at']) ?></small>
            </div>
          </div>
          <?php if ($isOwner): ?>
            <button class="delete-post" data-id="<?= (int)$p['id'] ?>" title="Delete post">🗑️</button>
          <?php endif; ?>
        </div>
        <p style="white-space:pre-wrap; margin-top:8px;"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
        <?php if ($postImage): ?>
          <div style="margin-top:8px;">
            <img src="<?= htmlspecialchars($postImage) ?>" alt="post image" style="max-width:100%; display:block;">
          </div>
        <?php endif; ?>
        
        <div class="post-actions" style="margin-top:8px;">
          <button class="btn-like" data-value="1">👍 <span class="likes-count"><?= $likes ?></span></button>
          <button class="btn-dislike" data-value="-1">👎 <span class="dislikes-count"><?= $dislikes ?></span></button>
        </div>
      </article>
      <?php endforeach; ?>
    </section>
  </main>
</div>

<!-- ===== Edit Profile Modal ===== -->
<div id="editProfileModal" class="modal-backdrop">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Profile</h3>
      <button id="closeEditModal" class="close-x">✕</button>
    </div>
    <form id="profileUpdateForm" enctype="multipart/form-data">
      <div class="grid-2">
        <div class="field full">
          <label>Full Name</label>
          <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        </div>
        <div class="field full">
          <label>Date of Birth</label>
          <input type="date" name="dob" value="">
          <div class="hint">set DOB Age will auto-update </div>
        </div>
        <div class="field full" style="text-align:center;">
          <div style="display:inline-block;">
            <div class="avatar-ring">
              <img id="editAvatarPreview"
                   src="<?= !empty($user['profile_pic']) ? htmlspecialchars(UPLOAD_URL . $user['profile_pic']) : 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2296%22 height=%2296%22><rect width=%2296%22 height=%2296%22 rx=%2248%22 fill=%22%23e9edf3%22/></svg>' ?>"
                   alt="preview" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <label for="editProfilePic" class="btn btn-secondary"  style="flex:1;background:green ;">Change Photo</label>
            <input type="file" id="editProfilePic" name="profile_pic" accept="image/*" style="display:none;">
          </div>
        </div>
      </div>
      <div style="display:flex; gap:8px; margin-top:14px;">
        <button type="submit" class="btn btn-secondary" style="flex:1;">Save</button>
        <button type="button" id="cancelEdit" class="btn" style="flex:1;background:#000;">Cancel</button>
      </div>
      <div id="profileUpdateMsg"></div>
    </form>
  </div>
</div>
<script>
  var CURRENT_USER_ID = <?= json_encode((int)$userId) ?>;
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
  // open/close modal
  $(document).on('click', '#editProfileBtn', function(){ $('#editProfileModal').fadeIn(120); });
  $(document).on('click', '#closeEditModal, #cancelEdit', function(){ $('#editProfileModal').fadeOut(120); });
  // live preview for new photo
  $(document).on('change', '#editProfilePic', function(e){
    const f = e.target.files && e.target.files[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    $('#editAvatarPreview').attr('src', url);
    $('#editAvatarPreview')[0].onload = () => URL.revokeObjectURL(url);
  });
  // submit update (fullname, dob->age, profile_pic)
  $(document).on('submit', '#profileUpdateForm', function(e){
    e.preventDefault();
    var fd = new FormData(this);
    $.ajax({
      url: 'ajax/update_profile.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json'
    }).done(function(res){
      var $msg = $('#profileUpdateMsg');
      if (res && res.success) {
        $msg.css('color','#2a9d8f').text(res.message || 'Profile updated');
        if (res.fullname) $('#profileName').text(res.fullname);
        if (typeof res.age !== 'undefined') $('#profileAge').text(res.age);
        if (res.profile_pic_url) $('#profileAvatar').attr('src', res.profile_pic_url);
        setTimeout(function(){ $('#editProfileModal').fadeOut(120); }, 500);
      } else {
        $msg.css('color','#d62828').text((res && res.message) ? res.message : 'Update failed');
      }
    }).fail(function(xhr){
      $('#profileUpdateMsg').css('color','#d62828').text('Update request failed.');
    });
  });
</script>
</body>
</html>

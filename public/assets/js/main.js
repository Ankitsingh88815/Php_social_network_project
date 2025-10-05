

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
function nl2br(str) {
  return (str || '').replace(/\n/g, '<br>');
}

$(document).ready(function () {
  console.log("✅ main.js loaded");


  $(document).on('submit', '#signupForm', function (e) {
    e.preventDefault();

    var $form = $(this);
    var $btn  = $('#signupBtn'); // if you have a button id, optional
    if ($btn.length) $btn.prop('disabled', true).text('Signing up…');

    // client-side re-password check (optional but good)
    var p1 = $('#pw1').length ? $('#pw1').val().trim() : ($('input[name="password"]').val() || '').trim();
    var p2 = $('#pw2').length ? $('#pw2').val().trim() : (
      $('input[name="re_password"]').val() || $('input[name="password2"]').val() || ''
    ).trim();
    if (!p1 || !p2 || p1 !== p2) {
      $('#signupMsg').removeClass('ok').addClass('err').text('Passwords do not match.');
      if ($btn.length) $btn.prop('disabled', false).text('Sign Up');
      return;
    }

    var fd = new FormData(this);
    if (!fd.has('re_password') && p2) fd.append('re_password', p2);

    $('#signupMsg').removeClass('err ok').text('');

    $.ajax({
      url: 'ajax/signup.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json'
    }).done(function (res) {
      console.log('Signup response:', res);
      if (res && res.success) {
        // ✅ No redirect. Fetch login page and swap into current DOM.
        fetch('index.php', { credentials: 'same-origin', cache: 'no-store' })
          .then(function (r) { return r.text(); })
          .then(function (html) {
            history.replaceState(null, '', 'index.php');   // URL update (no navigation)
            document.open();
            document.write(html);
            document.close();
          })
          .catch(function (err) {
            console.error('Fetch login failed:', err);
            // fallback: normal navigation
            location.replace('index.php');
          });
      } else {
        $('#signupMsg')
          .removeClass('ok').addClass('err')
          .text((res && res.message) ? res.message : 'Signup failed.');
      }
    }).fail(function (xhr) {
      console.error('Signup raw:', xhr.responseText);
      $('#signupMsg').removeClass('ok').addClass('err').text('Signup request failed.');
    }).always(function () {
      if ($btn.length) $btn.prop('disabled', false).text('Sign Up');
    });
  });

$(document).on('keydown', '#loginForm', function(e){
  if (e.key === 'Enter') { e.preventDefault(); $('#loginBtn').click(); }
});

$(document).on('click', '#loginBtn', function () {
  var $btn  = $(this);
  var $form = $('#loginForm');

  $btn.prop('disabled', true).text('Logging in…');
  $('#loginMsg').removeClass('ok err').text('');

  $.post('ajax/login.php', $form.serialize(), function (res) {
    if (res && res.success) {
      // 1) profile.php ko fetch karo (same-origin session ab set ho chuka hai)
      fetch('profile.php', { credentials: 'same-origin' })
        .then(function(r){ return r.text(); })
        .then(function(html){
          // 2) URL ko profile.php se replace karo (no full navigation)
          history.replaceState(null, '', 'profile.php');

          // 3) Current document ko replace karo—no white flash/jump
          document.open();
          document.write(html);
          document.close();
        })
        .catch(function(err){
          console.error('Fetch profile failed:', err);
          // Fallback: agar fetch fail ho jaye to normal navigation
          location.replace('profile.php');
        });

    } else {
      $('#loginMsg')
        .addClass('err')
        .css({color:'#d62828', fontWeight:'600'})
        .text((res && res.message) ? res.message : 'Invalid credentials.');
    }
  }, 'json')
  .fail(function () {
    $('#loginMsg').css({color:'#d62828'}).text('Login request failed.');
  })
  .always(function () {
    $btn.prop('disabled', false).text('Login');
  });
});


$(document).on('click', '#signupLink', function (e) {
  e.preventDefault(); // stop normal navigation

  fetch('signup.php', { credentials: 'same-origin', cache: 'no-store' })
    .then(function (r) { return r.text(); })
    .then(function (html) {
      // URL update kare bina reload
      history.replaceState(null, '', 'signup.php');
      document.open();
      document.write(html);
      document.close();
    })
    .catch(function (err) {
      console.error('Fetch signup failed:', err);
      // fallback normal navigation
      location.href = 'signup.php';
    });
});

  /* -----------------------
   LOGOUT (pure AJAX + SPA-style page swap)
----------------------- */
$(document).on('click', '#logoutBtn', function () {
  var $btn = $(this);
  $btn.prop('disabled', true).text('Logging out…');

  $.post('ajax/logout.php', {}, function (res) {
    // res: { success: true } expected
  }, 'json')
  .always(function () {
    // Session ab clear ho chuki hogi. Index ko fetch karke swap karo.
    fetch('index.php', { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        history.replaceState(null, '', 'index.php');
        document.open(); document.write(html); document.close();
      })
      .catch(function (err) {
        console.error('Fetch index failed:', err);
        // Fallback: normal navigation
        location.replace('index.php');
      });
  });
});


  $('#addImageBtn').on('click', function () {
    $('#postImage').trigger('click');
  });

  $('#postImage').on('change', function (e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    $('#imagePreview').attr('src', url);
    $('#imagePreviewBox').removeClass('hidden');

    // free memory after display
    $('#imagePreview')[0].onload = function () { URL.revokeObjectURL(url); };
  });

  $('#removeImageBtn').on('click', function () {
    const $input = $('#postImage');
    $input.val(''); // clear the file
    $('#imagePreview').attr('src', '');
    $('#imagePreviewBox').addClass('hidden');
  });

  /* -----------------------
     CREATE POST (requires description OR image)
  ----------------------- */
  
  $('#postForm').on('submit', function (e) {
  e.preventDefault();

  var $form = $(this);
  var desc = $.trim($form.find('textarea[name="description"]').val());
  var fileCount = ($form.find('#postImage')[0] || $form.find('input[name="image"]')[0] || { files: [] }).files.length;

  if (!desc && fileCount === 0) {
    alert("Please add a description or choose an image before posting.");
    return;
  }

  var fd = new FormData(this);

  $.ajax({
    url: 'ajax/create_post.php',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json'
  }).done(function (res) {
    if (res && res.success && res.post) {
      var p = res.post;

      var postHtml =
        '<article class="post card" data-post-id="' + p.id + '">' +
          '<div class="post-header" style="display:flex;justify-content:space-between;align-items:center;">' +
            '<div style="display:flex; align-items:center; gap:8px;">' +
              (p.profile_pic_url
                ? '<img src="' + p.profile_pic_url + '" alt="avatar" style="width:40px;height:40px;border-radius:50%;">'
                : '<div style="width:40px;height:40px;border-radius:50%;background:#ddd;"></div>') +
              '<div>' +
                '<strong>' + escapeHtml(p.fullname) + '</strong><br>' +
                '<small>' + escapeHtml(p.created_at || '') + '</small>' +
              '</div>' +
            '</div>' +
            ((typeof CURRENT_USER_ID !== 'undefined' && p.user_id == CURRENT_USER_ID)
              ? '<button class="delete-post" data-id="' + p.id + '">🗑️</button>'
              : '') +
          '</div>' +
          (p.image_url
            // 👇 Strict sizing, cover crop with CSS class
            ? '<div style="margin-top:8px;">'
              + '<img src="' + p.image_url + '" alt="post image" class="dynamic-post-image" style="max-width:100%;max-height:320px;width:100%;object-fit:cover;border-radius:8px;display:block;">'
              + '</div>'
            : '') +
          '<p style="white-space:pre-wrap; margin-top:8px;">' + nl2br(escapeHtml(p.description || "")) + '</p>' +
          '<div class="post-actions" style="margin-top:8px;">' +
            '<button class="btn-like" data-value="1">👍 <span class="likes-count">' + (p.likes || 0) + '</span></button>' +
            '<button class="btn-dislike" data-value="-1">👎 <span class="dislikes-count">' + (p.dislikes || 0) + '</span></button>' +
          '</div>' +
        '</article>';

      $('#posts').prepend(postHtml);

      // reset form + clear preview UI
      $('#postForm')[0].reset();
      $('#imagePreview').attr('src', '');
      $('#imagePreviewBox').addClass('hidden');

    } else if (res && res.success) {
      location.reload();
    } else {
      alert(res && res.message ? res.message : 'Error creating post');
    }
  }).fail(function (xhr) {
    alert("Post request failed.");
  });
});

// Utility functions (already must be present)
function escapeHtml(text) {
  return (text + '').replace(/[<>&'"]/g, function (c) {
    return {'<':'&lt;','>':'&gt;','&':'&amp;','\'':'&#39;','"':'&quot;'}[c];
  });
}
function nl2br(str) {
  return (str + '').replace(/\n/g, '<br>');
}


  /* -----------------------
     DELETE POST (delegated)
  ----------------------- */
  $('#posts').on('click', '.delete-post', function () {
    if (!confirm('Remove this post?')) return;
    var id = $(this).data('id');
    console.log("Delete clicked id:", id);

    $.post('ajax/delete_post.php', { post_id: id }, function (res) {
      console.log("Delete response:", res);
      if (res && res.success) {
        var $post = $('*[data-post-id="' + id + '"]').first();
        $post.slideUp(250, function () { $post.remove(); });
      } else {
        alert(res && res.message ? res.message : 'Error deleting post');
      }
    }, 'json').fail(function (xhr) {
      console.error("Raw response:", xhr.responseText);
      alert("Delete request failed.");
    });
  });

  /* -----------------------
     LIKE / DISLIKE
  ----------------------- */
  $('#posts').on('click', '.btn-like, .btn-dislike', function () {
    var btn = $(this);
    var post = btn.closest('.post');
    var postId = post.data('post-id');
    var value = parseInt(btn.data('value'), 10);

    $.post('ajax/toggle_like.php', { post_id: postId, value: value }, function (res) {
      if (res && res.success) {
        post.find('.likes-count').text(res.likes);
        post.find('.dislikes-count').text(res.dislikes);
      } else {
        alert(res && res.message ? res.message : 'Error');
      }
    }, 'json').fail(function (xhr) {
      console.error("Toggle like raw:", xhr.responseText);
      alert("Like/Dislike request failed.");
    });
  });

  /* -----------------------
     EDIT PROFILE (modal + preview + save)
  ----------------------- */
  $(document).on('click', '#editProfileBtn', function () {
    $('#editProfileModal').fadeIn(120);
  });
  $(document).on('click', '#closeEditModal, #cancelEdit', function () {
    $('#editProfileModal').fadeOut(120);
  });

  // live preview for profile photo
  $(document).on('change', '#editProfilePic', function (e) {
    const f = e.target.files && e.target.files[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    $('#editAvatarPreview').attr('src', url);
    $('#editAvatarPreview')[0].onload = () => URL.revokeObjectURL(url);
  });

  // submit profile update
  $(document).on('submit', '#profileUpdateForm', function (e) {
    e.preventDefault();
    var fd = new FormData(this);

    $.ajax({
      url: 'ajax/update_profile.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json'
    }).done(function (res) {
      console.log('Update profile response:', res);
      var $msg = $('#profileUpdateMsg');
      if (res && res.success) {
        $msg.css('color', '#2a9d8f').text(res.message || 'Profile updated');
        if (res.fullname) $('#profileName').text(res.fullname);
        if (typeof res.age !== 'undefined') $('#profileAge').text(res.age);
        if (res.profile_pic_url) $('#profileAvatar').attr('src', res.profile_pic_url);
        setTimeout(function () { $('#editProfileModal').fadeOut(120); }, 500);
      } else {
        $msg.css('color', '#d62828').text((res && res.message) ? res.message : 'Update failed');
      }
    }).fail(function (xhr) {
      console.error('Update profile raw:', xhr.responseText);
      $('#profileUpdateMsg').css('color', '#d62828').text('Update request failed.');
    });
  });

});

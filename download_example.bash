#!/bin/bash

# php copy_git.php {owner} {repo} {path_to_data} {true if you need to get the full path when download something or false} {github access token (optional) if you want to get more api call}

php copy_git.php mlewand ckeditor5 packages/ckeditor5-image/src/imageupload.js:path_if_you_have_dst_folder_end_with_slash,packages/ckeditor5-image/src/imagestyle.js true

php copy_git.php mlewand ckeditor5 packages:ck_editor/
<div class="wrapper">
    <div id="editor-container">
        <textarea id="editor" style="display: none">
            <?= htmlspecialchars($article['body']) ?>
        </textarea>
        <div id="sidebar"></div>
    </div>
</div>

<script src="/frontend/build/ckeditor.js"></script>
<script src="/frontend/src/userDataLoader.js"></script>
<script src="/frontend/src/customCommentsAdapter.js"></script>
<script src="/frontend/src/customTrackChangesAdapter.js"></script>
<script>
	const EXAMPLE = {
		ARTICLE_ID: <?=(int)$article['id']?>,
		CURRENT_USER_ID: <?=json_encode($currentUserId)?>,
		USERS: <?=json_encode($users)?>,
		CSRF_TOKEN: <?=json_encode($_SESSION['csrf_token'])?>
	};

	ClassicEditor.create(document.querySelector('#editor'), {
		extraPlugins: [ UserDataLoader, CustomCommentsAdapter, CustomTrackChangesAdapter ],
		licenseKey: <?=json_encode(LICENSE_KEY)?>,
		sidebar: {
			container: document.querySelector('#sidebar')
		},
		autosave: {
			save(editor) {
				const formData = new FormData();
				formData.append('body', editor.getData());
				formData.append('csrf_token', EXAMPLE.CSRF_TOKEN);

				return fetch('/article/save/' + EXAMPLE.ARTICLE_ID, {
					method: "POST",
					body: formData
				});
			}
		}
	})
		.then(editor => {
			window.editor = editor;
		})
		.catch(err => {
			console.error(err.stack);
		});
</script>

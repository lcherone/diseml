<div class="card card-nav-tabs" id="port-forwards">
	<div class="card-header" data-background-color="blue">
		<h4 class="title"><i class="fa fa-globe"></i> Submit</h4>
		<p class="category">If you know of any throw away email services please submit them using the form below.</p>
	</div>
	<div class="card-content">
	    <?php if (!empty($form['errors']['global'])): ?>
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <?= $form['errors']['global'] ?>
        </div>
        <?php endif ?>
        <form method="post" action="/submit" novalidate="" id="form">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
        	<div class="form-group label-floating">
        		<label for="single" class="control-label">Enter a single domain.</label>
        		<input type="text" id="single" class="form-control<?= (!empty($form['errors']['single']) ? ' form-control-invalid' : null) ?>" name="single" value="<?= (!empty($form['values']['single']) ? htmlentities($form['values']['single']) : '') ?>">
                <?php if (!empty($form['errors']['single'])): ?><div class="invalid-feedback"><?= $form['errors']['single'] ?></div><?php endif ?>
        	</div>
        	<div class="form-group label-floating">
        		<label for="multi" class="control-label">Enter multiple domains, each domain on a new line.</label>
        		<textarea id="multi" class="form-control<?= (!empty($form['errors']['multi']) ? ' form-control-invalid' : null) ?>" rows="7" style="min-height:230px" name="multi"><?= (!empty($form['values']['multi']) ? htmlentities($form['values']['multi']) : '') ?></textarea>
                <div id="line-count" class="mt-2">Max domains: 3000/<span id="lines-used">0</span></div>
                <?php if (!empty($form['errors']['multi'])): ?><div class="invalid-feedback"><?= $form['errors']['multi'] ?></div><?php endif ?>
        	</div>
        	<button type="submit" class="btn btn-primary form-submit">Submit</button>
        </form>
	</div>
</div>
<?php ob_start() ?>
<script>
$(document).ready(function() {

    var allow = true;
    var lines = 3000;
    var linesUsed = $('#lines-used');

    $('#line-count').hide();
    $('#multi').off('click focus paste keyup keydown change').on('click focus paste keyup keydown change', function(e) {
        var newLines = $(this).val().split("\n").length;
        linesUsed.text(newLines);
        if (newLines > 2000) {
            $('#line-count').show();
        }
        if (newLines >= lines) {
            linesUsed.css('color', 'red');
            $('.form-submit').attr("disabled", true).addClass('disabled');
            allow = false;
            if (e.keyCode != 8 && e.keyCode != 46) {
                return false;
            }
        }
        else {
            allow = true;
            $('.form-submit').attr("disabled", false).removeClass('disabled');
            linesUsed.css('color', '');
        }
    });
    
    $('#form').submit (function(e) {
        return allow;
    });

    $('#single').on('keydown paste', function() {
        $('#multi').val('').attr('disabled', true).parent().hide();
    });
    $('#multi').on('keydown paste', function() {
        $('#single').val('').attr('disabled', true).parent().hide();
    });
});
</script>
<?php $f3->set('javascript', $f3->get('javascript').ob_get_clean()) ?>

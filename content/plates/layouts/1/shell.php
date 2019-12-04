<div class="course_head">
    <div class="course_head_inner container">
        <h1><?=$course_data['name']?></h1>
    </div>
</div>
<div class="container content-container">
    <div class="row course_content">
        <?php
        // If the user has completed the course the reset and certificate buttons will appear.
        if ($page_data['data']['cert_data']['has_certificate']) : ?>
        <div class="alert alert-info clearfix reset_and_cert_buttons">
            <p class="pull-left"><strong>Well Done!</strong>  You have completed the assessment for this course.</p>
            <a class="btn pull-right btn-success" href="<?=$constants['base_url']?>api/cert/download/<?=$page_data['data']['cert_data']['course_path']?>/<?=$page_data['data']['cert_data']['user_id']?>/<?=$page_data['data']['cert_data']['assessment_id']?>">Download Certificate (PDF)</a>
            <a class="btn pull-right btn-danger" href ='#reset_modal' data-toggle="modal">Reset Course</a>
        </div>
        <?php // Modal. ?>
        <div id="reset_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h3 id="myModalLabel">Are you sure you want to reset this course?</h3>
            </div>
            <div class="modal-body">
                <p>All of your assessments for this course will be reset so you can re-complete it again from the beginning.</p>
                <p><strong>You will not be able access previous certificates for this course once it has been reset.</strong></p>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                <button class="btn btn-danger reset_button" href="reset_course.php">Reset</button>
            </div>
        </div>
        <?php endif;?>

        <?php if (isset($navigation)) : ?>
        <nav class="span3">
            <?php
            // Next and Back Buttons inside navigation.
            $this->insert('partials/prev_next');
            // Navigation.
            echo $navigation;
            ?>
        </nav>
        <?php endif; ?>

        <div id="maincontent" class="span9 course_body">
            <?php if (isset($page_data['data']['assessment_summary'])) : ?>
                <?php $this->insert('partials/assessment_summary'); ?>
            <?php else if (isset($page_data['content'])) : ?>
                <?=$page_data['content']?>
            <?php endif; ?>
        </div>
        <?php // Next and back buttons. ?>
        <div class="span12">
            <div class="span3 bottom-pn">
                <?php $this->insert('partials/prev_next'); ?>
            </div>
        </div>

    </div>
</div>

<div class="module-testimonials">
    <?php if ($this->entries): ?>
        <ul class="list-group testimonials-list-shortcode">
            <?php foreach ($this->entries as $postObject): ?>
                <li class="list-group-item">
                    <?= $postObject->post_title; ?>
                    <?= $postObject->post_content; ?>

                    <h6>Images</h6>
                    <?php foreach ($postObject->images as $image): ?>
                        <p>Image Name: <?= $image->post_title; ?></p>
                        <?= $image->resized->thumbnail; ?>
                        <?= $image->resized->medium; ?>
                        <?= $image->resized->medium_large; ?>
                        <?= $image->resized->large; ?>
                        <?= $image->resized->homepage; ?>
                    <?php endforeach; ?>

                    <h6>Custom Meta</h6>
                    <?= $postObject->meta->_edit_last; ?>
                    <?= $postObject->meta->_edit_lock; ?>

                </li>
            <?php endforeach; ?>
        </ul>

        <?= $this->paginationAdvanced(); ?>
        <?= $this->paginationSimple(); ?>
    <?php endif; ?>
</div>
<?php

/** @var string[] $items */
/** @var string $hash */

$currentItems = [];

if (isset($_COOKIE['nicknames'])) {
    $cookie = stripslashes($_COOKIE['nicknames']);
    $currentItems = json_decode($cookie, true);
}
?>
<div class="nickname-generator ai-nickname-results">
    <div id="nicknames-title" class="d-block">
        <div class="row">
            <div class="col-12">
                <h3><?php esc_html_e('Your Nicknames', 'ai_nickname_generator'); ?></h3>
            </div>
        </div>
    </div>

    <div id="nicknames-sorting" data-current="random" class="d-block">
        <div class="row">
            <div class="col-lg-6 col-12 button-wrapper">
                <button class="sortingbutton nickname-generator-run" data-order="alphabet">
                    <?php esc_html_e('Sort Alphabetically', 'ai_nickname_generator'); ?>
                </button>
            </div>

            <div class="col-lg-6 col-12 button-wrapper">
                <button class="sortingbutton nickname-generator-run active" data-order="random">
                    <?php esc_html_e('Random Sort', 'ai_nickname_generator'); ?>
                </button>
            </div>
        </div>
    </div>

    <div id="nicknames-list" class="row ai-nickname-results-list">
        <?php foreach ($items as $item) : ?>
            <div class="nickname-wrapper col-lg-4 col-md-4 col-12" data-nickname="<?php echo esc_html($item); ?>">
                <div class="nickname" id="nickname-<?php echo md5($item); ?>">
                    <span class="addtobasket<?php echo in_array($item, $currentItems) ? ' active' : ''; ?>" title="Add Nickname to your list" data-nickname="<?php echo esc_html($item); ?>" data-hash="<?php echo $postId; ?>:<?php echo $hash; ?>">
                        <i class="far fa-heart" aria-hidden="true"></i><i class="fas fa-heart" aria-hidden="true"></i>
                    </span>
                    <div class="nickname-title">
                        <h2><?php echo $item; ?></h2>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($items) >= $perPage) : ?>
        <div class="mb-3">
            <button class="load-more nickname-generator-more" id="load-more-nicknames" data-page="1" data-order="alphabet">
                Load more Nicknames
            </button>
        </div>
    <?php endif; ?>


    <div class="row">
        <div class="col-6">
            <div class="selected-button d-block mb-3">
                <button id="nickname-generator-basket" data-bs-toggle="modal" data-bs-target="#basket-modal">
                    <?php
                    $currentlist = stripslashes($_COOKIE['nicknames'] ?? '[]');
                    $currentarr = json_decode($currentlist, true);
                    $count = count($currentarr ?: []);

                    ?>
                    Send my Nicknames <span class="count"><?php if ($count > 0) { ?> (<?php echo $count; ?>) <?php } ?></span>
                </button>
            </div>
        </div>

        <div class="col-6">
            <div class="selected-button d-block mb-3">
                <button id="nickname-generator-copy">
                    Copy All to Clipboard
                </button>
            </div>
        </div>
    </div>

</div>
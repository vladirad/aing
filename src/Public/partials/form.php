<?php
if (!defined('WPINC')) {
    die;
}

/** @var \AbacusPlus\AiNicknameGenerator\Public\Form $this */

$formData = $this->formData;

$attributes = $formData->formAttributes;

$showTitle = $formData->showTitle;

if($showTitle == 'false') {
    $postTitle = null;
} else {
    $postTitle = $this->post->post_title;
}
?>

<div class="ai-nickname-form-wrapper">
    <form action="/" method="POST" class="ai-nickname-form" data-id="<?php echo $this->post->ID; ?>">
        <div class="<?php echo $attributes->wrapperClass; ?>">
            <?php if ($postTitle) : ?>
                <<?php echo $showTitle; ?>><?php echo $postTitle; ?></<?php echo $showTitle; ?>>
            <?php endif; ?>
            <?php foreach ($formData->fields as $field) : ?>
                <?php
                if ($field->name) :
                    $classes = $attributes->merge($field->classNames);

                    echo '<div class="' . ($field->type === 'select' ? $classes->selectWrapperClass : $classes->inputWrapperClass) . '">';

                    if ($field->label) :
                        echo sprintf(
                            '<label class="%s">%s%s</label>',
                            $classes->labelClass,
                            $field->label,
                            $field->required ? ' <span class="required">*</span>' : ''
                        );
                    endif;

                    switch ($field->type):
                        case 'inputText':
                        case 'inputNumber':

                            echo sprintf(
                                '<input type="%s" class="%s" placeholder="%s" name="%s" />',
                                $field->type === 'inputText' ? 'text' : 'number',
                                $classes->inputClass,
                                $field->placeholder,
                                $field->name,
                            );

                            break;

                        case 'select':

                            $optionsArray = [];

                            foreach ($field->options as $option) {
                                if (empty($option['label'])) {
                                    continue;
                                }

                                $optionsArray[] = sprintf(
                                    '<option value="%s">%s</option>',
                                    $option['value'] ?: $option['label'],
                                    $option['label'],
                                );
                            }

                            if (empty($optionsArray)) {
                                break;
                            }

                            echo sprintf(
                                '<select class="%s" name="%s">%s%s</select>',
                                $classes->selectClass,
                                $field->name,
                                $field->placeholder ? '<option value="">' . $field->placeholder . '</option>' : '',
                                implode('', $optionsArray),
                            );

                            break;

                        default:
                            break;
                    endswitch;

                    echo '<div class="invalid-feedback">test</div>';

                    echo '</div>';
                endif;
                ?>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="<?php echo $attributes->buttonClass; ?>">Generate</button>
    </form>

    <div class="ai-nickname-form-results"></div>

    <div class="ai-nickname-loading mb-3" style="display: none;">
        <span class="your">Loading your results</span>
        <span class="more">Loading more results</span>
        <span class="dots">...</span>
    </div>
</div>
<input type="hidden" name="interactive-map-image-id" value="<?php echo $current['id']; ?>">
<input type="hidden" name="interactive-map-is-selected" value="<?php echo !empty($current['id']) || !empty($current['layers']) ? 1 : 0; ?>">

<div class="map-pin-toolbox">
    <button class="button" type="button" data-action="interactive-map-add-layer"><i class="fa fa-clone"></i> <?php _e('Add layer', 'modularity-interactive-map'); ?></button>
    <button class="button" type="button" data-action="interactive-map-add-pin" data-map-editor><i class="fa fa-map-marker"></i> <?php _e('Add pin', 'modularity-interactive-map'); ?></button>
</div>

<ol id="map-layers"><?php if (is_array($current['layers']))  : foreach ($current['layers'] as $layer) : ?>
    <li data-layer-id="<?php echo $layer['id']; ?>" data-layer-category="<?php echo isset($layer['category']) && is_array($layer['category']) ? implode('|', $layer['category']) : ''; ?>">
        <input type="hidden" name="interactive-map-layers[<?php echo $layer['id']; ?>][id]" value="<?php echo $layer['id']; ?>">
        <input type="text" name="interactive-map-layers[<?php echo $layer['id']; ?>][name]" value="<?php echo $layer['name']; ?>">

        <div class="actions">
            <button type="button" class="button button-link" data-action="interactive-map-toggle-layer" data-layer-id="<?php echo $layer['id']; ?>"><span class="dashicons dashicons-hidden"></span></button>
            <button type="button" class="button button-link" data-action="interactive-map-remove-layer" data-layer-id="<?php echo $layer['id']; ?>"><span class="dashicons dashicons-trash"></span></button>
        </div>
    </li>
<?php endforeach; endif; ?></ol>

<div class="map-container">
<?php
if (is_array($current['layers'])) {
    foreach ($current['layers'] as $layer) {
        $imageSrc = wp_get_attachment_url($layer['id']);
        echo '<img src="', $imageSrc, '" data-layer-id="' . $layer['id'] . '">';
    }
} elseif ($current['id']) {
    $imageSrc = wp_get_attachment_url($current['id']);
    echo '<img src="', $imageSrc, '">';
} else {
    echo '<span class="no-map">' . __('No map image selected', 'modularity-interactive-map') . '</span>';
}

if ($current['pins']) {
    echo '<script>jQuery(document).ready(function() {';

    foreach ($current['pins'] as $pin) {
        echo 'ModularityInteractiveMap.MapPins.addPin(
                    \'' . $pin['top'] . '\',
                    \'' . $pin['left'] . '\',
                    \'' . $pin['title'] . '\',
                    \'' . $pin['link'] . '\',
                    \'' . preg_replace('/\s+/', ' ',trim($pin['text'])) . '\',
                    \'' . $pin['category'] . '\'
            );';
    }

    echo '});</script>';
}
?>
</div>

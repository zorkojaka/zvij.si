<?php
/** Verify transparent logo interiors without changing the original geometry. */
if (PHP_SAPI !== 'cli') {
    exit(1);
}
$theme = dirname(__DIR__) . '/wp-content/themes/zvij-theme';
$original = new DOMDocument();
$outline = new DOMDocument();
$original->load($theme . '/assets/images/brand/logo-zvij.svg');
$outline->load($theme . '/assets/images/brand/logo-zvij-outline.svg');
$source = $original->getElementsByTagName('path');
$paths = $outline->getElementsByTagName('path');
assert($source->length === $paths->length);
foreach ($paths as $index => $path) {
    if ($path->getAttribute('d') !== $source->item($index)->getAttribute('d')
        || $path->getAttribute('transform') !== $source->item($index)->getAttribute('transform')
        || $path->getAttribute('fill') === 'rgb(100%, 100%, 100%)'
        || $path->getAttribute('stroke') === 'rgb(100%, 100%, 100%)') {
        throw new RuntimeException('Logo geometry or transparent interior check failed');
    }
}
foreach (['header.php', 'footer.php'] as $template) {
    if (! str_contains(file_get_contents($theme . '/' . $template), 'logo-zvij-outline.svg')) {
        throw new RuntimeException("$template must use transparent outline logo");
    }
}
echo "PASS original vector paths and transforms preserved\n";
echo "PASS opaque interior fills removed\n";
echo "PASS header and footer use outline logo\n";

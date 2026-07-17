<?php
function renderBreadcrumbs($items = []) {
    if (empty($items)) {
        return '';
    }

    $html = '<nav aria-label="breadcrumb" class="mb-4"><ol class="breadcrumb">';
    $count = count($items);
    foreach ($items as $index => $item) {
        $label = e($item['label'] ?? '');
        $url = $item['url'] ?? '';
        $isLast = $index === $count - 1;
        if ($isLast) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . $label . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . e($url) . '" class="text-decoration-none">' . $label . '</a></li>';
        }
    }
    $html .= '</ol></nav>';
    return $html;
}

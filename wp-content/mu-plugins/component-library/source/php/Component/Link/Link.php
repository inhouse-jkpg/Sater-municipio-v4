<?php

namespace ComponentLibrary\Component\Link;

class Link extends \ComponentLibrary\Component\BaseController
{
    public function init()
    {

        //Extract array for eazy access (fetch only)
        extract($this->data);

        //Link
        if ($href) {
            $this->data['attributeList']['href'] = $this->sanitizeHref($href);
        }

        //Target
        if ($target && $href) {
            $this->data['attributeList']['target'] = $target;
        }

        //XFN
        if ($xfn) {
            $this->data['attributeList']['rel'] = $xfn;
        }
    }

    /**
     * Sanitize the href attribute
     * 
     * This will format phone numbers correctly (mailto: only strips whitespace)
     * 
     * @param string $href  The href attribute
     * 
     * @return string       The sanitized href
     */
    private function sanitizeHref(?string $href): string
    {
        if(empty($href)) {
            return '';
        }
        $scheme = parse_url($href, PHP_URL_SCHEME);
        $value = substr($href, strlen($scheme) + 1);

        return match ($scheme) {
            'mailto' => $scheme . ':' . preg_replace('/\s+/', '', $value),
            'tel' => $scheme . ':' . preg_replace('/\s+|-/', '', $value),
            default => $href,
        };
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CrawlerService
{
    private const SUCCESS_CODE = 200;
    private const USER_AGENT
        = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36';
    private const REFERER = 'https://google.com/';

    /**
     * @var float|null
     */
    private ?float $pageLoadingTime = null;

    /**
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    public function handle(Request $request): array
    {
        $url = $request->input('url');

        if ($this->getUrlStatus($url) !== self::SUCCESS_CODE) {
            throw ValidationException::withMessages(
                ['url' => 'The URL ' . $url . ' is not available at the moment. Please try again']
            );
        }

        $html = $this->getUrlContent($url);
        $crawledPages[] = $this->prepareCrawledData($html, $url);
        $this->processLinks($request, $html, $crawledPages);

        return [
            'pagesData' => array_map(function ($page) {
                return [
                    'href' => $page['href'],
                    'page_title' => $page['page_title'],
                    'code' => $page['code'],
                    'link_type' => $page['link_type'],
                    'loading_time' => $page['loading_time'],
                    'word_qty' => $page['word_qty'],
                    'total_words' => $page['total_words'],
                    'images' => $page['images'],
                    'image_qty' => $page['image_qty'],
                    'links_qty' => $page['links_qty']
                ];
            }, $crawledPages),
            'generalData' => $this->getGeneralData($request, $crawledPages)
        ];
    }

    /**
     * @param string $url
     * @return int
     */
    private function getUrlStatus(string $url): int
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_NOBODY, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_REFERER, self::REFERER);

        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $httpCode;
    }

    /**
     * Return the HTML of URL
     *
     * @param string $url
     * @return string
     */
    private function getUrlContent(string $url): string
    {
        $startLoading = microtime(true);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_REFERER, self::REFERER);

        $html = curl_exec($curl);
        curl_close($curl);
        $this->pageLoadingTime = round((microtime(true) - $startLoading), 3);

        return $html;
    }

    /**
     * @param Request $request
     * @param string $html
     * @param array $crawledPages
     * @return void
     */
    private function processLinks(
        Request $request,
        string $html,
        array &$crawledPages
    ): void {
        $url = $request->input('url');
        $pageQty = $request->input('pages');

        $parseMainUrl = parse_url($url);
        $baseMainUrl = $parseMainUrl['scheme'] . '://' . $parseMainUrl['host'];

        $pageContent = $this->getBodyHtml($html);
        preg_match_all('~href=["\']?([^"\'>]+)["\']?~', $pageContent, $matches);

        $i = 1;
        foreach ($matches[1] ?? [] as $link) {
            if ($i >= $pageQty) {
                break;
            }

            if (!trim($link) || $link[0] === '#') {
                continue;
            }

            $linkHref = $link;
            $parseCurrent = parse_url($link);

            if (!isset($parseCurrent['scheme']) || !isset($parseCurrent['host'])) {
                $linkHref = $baseMainUrl . $parseCurrent['path'];
                $parseCurrent = parse_url($linkHref);
            }


            if (rtrim($linkHref, '/') === rtrim($url, '/')){
                continue;
            }

            $this->pageLoadingTime = null;
            $httpCode = $this->getUrlStatus($linkHref);
            $linkHtml = $this->getUrlContent($linkHref);

            $linkType = $parseCurrent['host'] === $parseMainUrl['host'] ? 'internal' : 'external';
            $crawledPages[] = $this->prepareCrawledData($linkHtml, $linkHref, $linkType, $httpCode);

            $i++;
            usleep(1000000);
        }
    }

    /**
     * @param string $html
     * @param string $url
     * @param string $linkType
     * @param int $status
     * @return array
     */
    private function prepareCrawledData(
        string $html,
        string $url,
        string $linkType = 'internal',
        int $status = self::SUCCESS_CODE
    ): array {
        $pageContent = $this->getBodyHtml($html);
        $wordsArray = array_count_values(str_word_count(strip_tags($pageContent), 1));

        preg_match_all('~<img[^>]*src=([\'"])(?<src>.+?)\1[^>]*>~i', $pageContent, $matches);
        $images = $matches['src'] ?? [];

        preg_match('~<title>([^<]*)<\/title>~im', $html, $matches);
        $title = $matches[1] ?? $url;

        preg_match_all('~href=["\']?([^"\'>]+)["\']?~', $pageContent, $matches);
        $allLinks = $matches[1] ?? [];

        return [
            'href' => $url,
            'page_title' => $title,
            'code' => $status,
            'link_type' => $linkType,
            'loading_time' => $this->pageLoadingTime,
            'words' => $wordsArray,
            'word_qty' => count($wordsArray),
            'total_words' => array_sum($wordsArray),
            'images' => $images,
            'image_qty' => count($images),
            'links' => $allLinks,
            'links_qty' => count($allLinks)
        ];
    }

    /**
     * @param Request $request
     * @param array $pagesData
     * @return array
     */
    private function getGeneralData(Request $request, array $pagesData): array
    {
        $url = $request->input('url');
        $pages = $request->input('pages');

        $titleLength = $titleQty = $pageLoad = 0;
        $allImages = $allWords = $allLinks = [];

        foreach ($pagesData as $page) {
            $allImages[] = $page['images'] ?? [];
            $allWords[] = $page['words'] ?? [];
            $allLinks[] = $page['links'] ?? [];

            $pageTitle = $page['page_title'] ?? '';
            $pageLoad += $page['loading_time'] ?? 0;

            if ($pageTitle) {
                $titleLength += strlen($pageTitle);
                $titleQty++;
            }
        }

        $internalLinks = $externalLinks = [];

        foreach (array_unique(array_merge([], ...$allLinks)) ?? [] as $link) {
            $link = trim($link);

            if (!$link) {
                continue;
            }

            if ($link[0] === '#') {
                $internalLinks[] = $link;
                continue;
            }

            $parseLink = parse_url($link);

            if (!isset($parseLink['scheme']) || !isset($parseLink['host'])) {
                $internalLinks[] = $link;
                continue;
            }

            $parseUrl = parse_url($url);

            if ($parseUrl['scheme'] . '://' . $parseUrl['host']
                === $parseLink['scheme'] . '://' . $parseLink['host']) {
                $internalLinks[] = $link;
            } else {
                $externalLinks[] = $link;
            }
        }

        return [
            'page_qty' => $pages,
            'unique_image_qty' => count(array_unique(array_merge([], ...$allImages))),
            'unique_internal_links' => count($internalLinks),
            'unique_external_links' => count($externalLinks),
            'load_page_average' => $pageLoad / $pages,
            'word_count_average' => count(array_unique(array_merge([], ...$allWords))) / $pages,
            'title_average' => $titleLength / $titleQty,
        ];
    }

    /**
     * @param string $html
     * @return string
     */
    private function getBodyHtml(string $html): string
    {
        preg_match('~<body>(.*)<\/body>~s', $html, $matches);
        $pageContentRaw = $matches[0] ?? '';
        return preg_replace('~<script[^>]*?>.*?</script>~si', '', $pageContentRaw);
    }
}

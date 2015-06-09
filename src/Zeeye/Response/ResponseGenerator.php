<?php

namespace Zeeye\Response;

/**
 * Trait used to provide convenient operations to create responses
 */
trait ResponseGenerator {

    /**
     * Create and return a RedirectResponse instance
     *
     * @param Url|array|string $url URL used for the redirection
     * @param integer $status an optional status code
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302) {
        $response = RedirectResponse::create($url);
        $response->setStatus($status);

        return $response;
    }

    /**
     * Create and return a HtmlResponse instance
     *
     * @param Zone|View|string $content the content of the response
     * @param integer $status an optional status code
     * @return HtmlResponse
     */
    public function html($content, $status = 200) {
        $response = HtmlResponse::create($content);
        $response->setStatus($status);

        return $response;
    }

    /**
     * Create and return a FileResponse instance
     *
     * @param string the content of the file or the file's path
     * @param string the content type
     * @return FileResponse
     */
    public function file($contentOrFilePath, $contentType = null) {
        return FileResponse::create($contentOrFilePath, $contentType);
    }

    /**
     * Create and return a JsonResponse instance
     *
     * @param array|string $content the content of the response
     * @return JsonResponse
     */
    public function json($content) {
        return JsonResponse::create($content);
    }

    /**
     * Create and return a TextResponse instance
     *
     * @param View|string $content the content of the response
     * @return TextResponse
     */
    public function text($content) {
        return TextResponse::create($content);
    }

}

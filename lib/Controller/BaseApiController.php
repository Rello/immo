<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;

abstract class BaseApiController extends Controller {
    public function __construct(string $appName, IRequest $request) {
        parent::__construct($appName, $request);
    }

    /**
     * @return array<string,mixed>
     */
    protected function getJsonBody(): array {
        $content = $this->request->getContent();
        if ($content === null || $content === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }
}

<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\BookingService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class BookingController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private BookingService $bookingService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $propId = $this->request->getParam('propId');
        $year = $this->request->getParam('year');
        return new DataResponse($this->bookingService->listByOwner($uid, $propId ? (int)$propId : null, $year ? (int)$year : null));
    }

    #[NoAdminRequired]
    public function get(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->bookingService->get($id, $uid));
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->bookingService->create($uid, $data);
        return new DataResponse($entity, 201);
    }

    #[NoAdminRequired]
    public function update(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->bookingService->update($id, $uid, $data);
        return new DataResponse($entity);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $this->bookingService->delete($id, $uid);
        return new DataResponse(['status' => 'ok']);
    }
}

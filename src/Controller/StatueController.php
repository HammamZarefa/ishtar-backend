<?php

namespace App\Controller;

use App\Request\ByIdRequest;
use App\Request\CreateStatueRequest;
use App\Request\DeleteRequest;
use App\Request\UpdateStatueRequest;
use App\Service\StatueService;
use App\Validator\StatueValidateInterface;
use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatueController extends BaseController
{
    private $statueService;

    /**
     * StatueController constructor.
     * @param $statueService
     */
    public function __construct(StatueService $statueService)
    {
        $this->statueService = $statueService;
    }

    /**
     * @Route("/statues", name="createStatue",methods={"POST"})
     * @param Request $request
     * @return
     */
    public function create(Request $request, StatueValidateInterface $statueValidate)
    {

        // Validation
        $validateResult = $statueValidate->statueValidator($request, 'create');
        if (!empty($validateResult))
        {
            $resultResponse = new Response($validateResult, Response::HTTP_OK, ['content-type' => 'application/json']);
            $resultResponse->headers->set('Access-Control-Allow-Origin', '*');
            return $resultResponse;
        }
        $data = json_decode($request->getContent(), true);
        $config = new AutoMapperConfig();
        $config->registerMapping(\stdClass::class, CreateStatueRequest::class);
        $mapper = new AutoMapper($config);
        $request = $mapper->map((object)$data, CreateStatueRequest::class);
        $result = $this->statueService->create($request);
        return $this->response($result, self::CREATE);
    }

    /**
     * @Route("/statue/{id}", name="updateStatue",methods={"PUT"})
     * @param Request $request
     * @return
     */
    public function update(Request $request, StatueValidateInterface $statueValidate)
    {
        $validateResult = $statueValidate->statueValidator($request, 'update');
        if (!empty($validateResult))
        {
            $resultResponse = new Response($validateResult, Response::HTTP_OK, ['content-type' => 'application/json']);
            $resultResponse->headers->set('Access-Control-Allow-Origin', '*');
            return $resultResponse;
        }
        $id=$request->get('id');
        $data = json_decode($request->getContent(), true);
        $config = new AutoMapperConfig();
        $config->registerMapping(\stdClass::class, UpdateStatueRequest::class);
        $mapper = new AutoMapper($config);
        $request = $mapper->map((object)$data, UpdateStatueRequest::class);
        $request->setId($id);
        $result = $this->statueService->update($request);
        return $this->response($result, self::UPDATE);
    }

    /**
     *  @Route("/statue/{id}", name="deleteStatue",methods={"DELETE"})
     * @param Request $request
     * @return
     */
    public function delete(Request $request)
    {
        $request=new DeleteRequest($request->get('id'));
        $result = $this->statueService->delete($request);
        return $this->response($result, self::DELETE);

    }

    /**
     * @Route("/statues", name="getAllStatue",methods={"GET"})
     * @return
     */
    public function getAll()
    {
        $result = $this->statueService->getAll();
        return $this->response($result,self::FETCH);
    }

    /**
     * @Route("/statue/{id}", name="getStatueById",methods={"GET"})
     * @param Request $request
     * @return
     */
    public function getStatueById(Request $request)
    {
        $request=new ByIdRequest($request->get('id'));
        $result = $this->statueService->getStatueById($request->getId());
        return $this->response($result,self::FETCH);
    }

}

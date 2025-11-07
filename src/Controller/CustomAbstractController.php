<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAbstractController extends AbstractController
{
    protected const DESERIALIZE_FORMAT = 'json';

    /**
     * Return Access Denied Error
     * @deprecated Use method error insted
     * @return JsonResponse Access Denied - http code 401
     */
    protected function accessDenied()
    {
        return $this->error('Access Denied.', [], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return Success response
     * 
     * @param String $message
     * @param Array $data
     * @param Int $response
     * @param Array $context
     * @return JsonResponse Success
     */
    protected function success(String $message, $data, int $response, array $context = []): JsonResponse
    {
        return $this->json(['success' => true, 'message' => $message, 'data' => $data], $response, [], $context);
    }

    /**
     * Return Error response
     * 
     * @param String $message
     * @param Array $data
     * @param Int $response
     * @return JsonResponse Success
     */
    protected function error(String $message, Array $data, int $response){

        return $this->json(['success' => false,'message' => $message, 'data' => $data], $response); 
    }
}
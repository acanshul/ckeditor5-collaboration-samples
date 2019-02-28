<?php

/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * This file is licensed under the terms of the MIT License (see LICENSE.md).
 */

namespace Example\Controllers;

use Example\Controller;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SuggestionController extends Controller
{
    /**
     * Acton that returns a suggestion with given id.
     *
     * @param RequestInterface $req
     * @param ResponseInterface $res
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function get(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $stmt = $this->db->prepare("SELECT * FROM suggestions WHERE id=:suggestion_id");
        $stmt->bindParam(':suggestion_id', $args['suggestion_id'], \PDO::PARAM_STR);
        $stmt->execute();

        return $this->json($stmt->fetch(\PDO::FETCH_ASSOC));
    }

    /**
     * Action that adds a new suggestion to the database.
     *
     * @param ServerRequestInterface $req
     *
     * @return ResponseInterface
     */
    public function add(ServerRequestInterface $req): ResponseInterface
    {
        $post = $req->getParsedBody();

        $requiredFields = ['id', 'article_id', 'type'];

        foreach ($requiredFields as $field) {
            if (empty($post[$field])) {
                return $this->error('Invalid request. Missing POST field: '.$field);
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO suggestions (id, article_id, user_id, type, created_at)
                        VALUES (:id, :article_id, :user_id, :type, :created_at)'
        );

        $currentUser = $this->getUserRepository()->getCurrentUser();
        $currentUserId = $currentUser['id'];

        $createdAt = time();

        $stmt->bindParam(':id', $post['id'], \PDO::PARAM_STR);
        $stmt->bindParam(':article_id', $post['article_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':type', $post['type'], \PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $currentUserId, \PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $createdAt, \PDO::PARAM_INT);

        $stmt->execute();

        return $this->json(
            [
                'id' => $post['id'],
                'created_at' => $createdAt
            ]
        );
    }
}

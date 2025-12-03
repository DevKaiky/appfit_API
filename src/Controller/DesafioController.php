<?php

namespace App\Controller;

use App\Service\DesafioService;
use Exception;

/**
 * Classe DesafioController
 * Responsável por receber requisições HTTP e retornar respostas JSON
 */
class DesafioController
{
    private DesafioService $desafioService;

    public function __construct()
    {
        $this->desafioService = new DesafioService();
    }

    /**
     * Cria um novo desafio
     * POST /desafios
     */
    public function criar(): void
    {
        try {
            $dados = $this->obterDadosRequisicao();

            // Adicionar ID do usuário autenticado (obtido do token JWT)
            $dados['criado_por'] = $_SERVER['USER_ID'] ?? null;

            $desafio = $this->desafioService->criar($dados);

            $this->responderSucesso($desafio, "Desafio criado com sucesso", 201);
        } catch (Exception $e) {
            $this->responderErro($e->getMessage());
        }
    }

    /**
     * Lista todos os desafios
     * GET /desafios
     */
    public function listarTodos(): void
    {
        try {
            $desafios = $this->desafioService->listarTodos();

            $this->responderSucesso($desafios, "Desafios recuperados com sucesso");
        } catch (Exception $e) {
            $this->responderErro("Não foi possível listar os desafios");
        }
    }

    /**
     * Busca um desafio por ID
     * GET /desafios/{id}
     */
    public function buscarPorId(int $id): void
    {
        try {
            $desafio = $this->desafioService->buscarPorId($id);

            $this->responderSucesso($desafio, "Desafio encontrado com sucesso");
        } catch (Exception $e) {
            $this->responderErro($e->getMessage(), 404);
        }
    }

    /**
     * Atualiza um desafio existente
     * PUT /desafios/{id}
     */
    public function atualizar(int $id): void
    {
        try {
            $dados = $this->obterDadosRequisicao();

            $desafio = $this->desafioService->atualizar($id, $dados);

            $this->responderSucesso($desafio, "Desafio atualizado com sucesso");
        } catch (Exception $e) {
            $this->responderErro($e->getMessage());
        }
    }

    /**
     * Exclui um desafio
     * DELETE /desafios/{id}
     */
    public function excluir(int $id): void
    {
        try {
            $this->desafioService->excluir($id);

            $this->responderSucesso(null, "Desafio excluído com sucesso");
        } catch (Exception $e) {
            $this->responderErro($e->getMessage());
        }
    }

    /**
     * Obtém os dados da requisição JSON
     *
     * @return array
     */
    private function obterDadosRequisicao(): array
    {
        $dados = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON inválido na requisição");
        }

        return $dados ?? [];
    }

    /**
     * Responde com sucesso
     *
     * @param mixed $dados
     * @param string $mensagem
     * @param int $codigo
     */
    private function responderSucesso($dados, string $mensagem, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'status' => 'sucesso',
            'mensagem' => $mensagem,
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    /**
     * Responde com erro
     *
     * @param string $mensagem
     * @param int $codigo
     */
    private function responderErro(string $mensagem, int $codigo = 400): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'erro' => $mensagem,
            'dado' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }
}

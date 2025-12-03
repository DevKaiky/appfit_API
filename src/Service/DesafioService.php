<?php

namespace App\Service;

use App\DAO\DesafioDAO;
use Exception;

/**
 * Classe DesafioService
 * Responsável pelas regras de negócio e validações relacionadas aos Desafios
 */
class DesafioService
{
    private DesafioDAO $desafioDAO;

    public function __construct()
    {
        $this->desafioDAO = new DesafioDAO();
    }

    /**
     * Cria um novo desafio após validações
     *
     * @param array $dados
     * @return array
     * @throws Exception
     */
    public function criar(array $dados): array
    {
        try {
            // Validar dados obrigatórios
            $this->validarDadosObrigatorios($dados);

            // Validar datas
            $this->validarDatas($dados['data_inicio'], $dados['data_fim']);

            // Validar nível de dificuldade
            $this->validarNivelDificuldade($dados['nivel_dificuldade']);

            // Criar desafio
            $id = $this->desafioDAO->criar($dados);

            // Buscar e retornar o desafio criado
            return $this->desafioDAO->buscarPorId($id);
        } catch (Exception $e) {
            error_log("Erro no service ao criar desafio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todos os desafios
     *
     * @return array
     * @throws Exception
     */
    public function listarTodos(): array
    {
        try {
            return $this->desafioDAO->listarTodos();
        } catch (Exception $e) {
            error_log("Erro no service ao listar desafios: " . $e->getMessage());
            throw new Exception("Não foi possível listar os desafios");
        }
    }

    /**
     * Busca um desafio por ID
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function buscarPorId(int $id): array
    {
        try {
            if ($id <= 0) {
                throw new Exception("ID inválido");
            }

            $desafio = $this->desafioDAO->buscarPorId($id);

            if (!$desafio) {
                throw new Exception("Desafio não encontrado");
            }

            return $desafio;
        } catch (Exception $e) {
            error_log("Erro no service ao buscar desafio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualiza um desafio existente
     *
     * @param int $id
     * @param array $dados
     * @return array
     * @throws Exception
     */
    public function atualizar(int $id, array $dados): array
    {
        try {
            if ($id <= 0) {
                throw new Exception("ID inválido");
            }

            // Verificar se desafio existe
            if (!$this->desafioDAO->existe($id)) {
                throw new Exception("Desafio não encontrado");
            }

            // Validar dados obrigatórios
            $this->validarDadosObrigatorios($dados, false);

            // Validar datas
            if (isset($dados['data_inicio']) && isset($dados['data_fim'])) {
                $this->validarDatas($dados['data_inicio'], $dados['data_fim']);
            }

            // Validar nível de dificuldade
            if (isset($dados['nivel_dificuldade'])) {
                $this->validarNivelDificuldade($dados['nivel_dificuldade']);
            }

            // Atualizar desafio
            $atualizado = $this->desafioDAO->atualizar($id, $dados);

            if (!$atualizado) {
                throw new Exception("Não foi possível atualizar o desafio");
            }

            // Retornar desafio atualizado
            return $this->desafioDAO->buscarPorId($id);
        } catch (Exception $e) {
            error_log("Erro no service ao atualizar desafio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exclui um desafio
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function excluir(int $id): bool
    {
        try {
            if ($id <= 0) {
                throw new Exception("ID inválido");
            }

            // Verificar se desafio existe
            if (!$this->desafioDAO->existe($id)) {
                throw new Exception("Desafio não encontrado");
            }

            $excluido = $this->desafioDAO->excluir($id);

            if (!$excluido) {
                throw new Exception("Não foi possível excluir o desafio");
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro no service ao excluir desafio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Valida se os dados obrigatórios estão presentes
     *
     * @param array $dados
     * @param bool $validarTodos
     * @throws Exception
     */
    private function validarDadosObrigatorios(array $dados, bool $validarTodos = true): void
    {
        $camposObrigatorios = ['titulo', 'descricao', 'data_inicio', 'data_fim', 'nivel_dificuldade'];

        if ($validarTodos) {
            foreach ($camposObrigatorios as $campo) {
                if (!isset($dados[$campo]) || trim($dados[$campo]) === '') {
                    throw new Exception("O campo '{$campo}' é obrigatório");
                }
            }
        }

        // Validações específicas de formato
        if (isset($dados['titulo'])) {
            if (strlen($dados['titulo']) < 5) {
                throw new Exception("O título deve ter no mínimo 5 caracteres");
            }
            if (strlen($dados['titulo']) > 150) {
                throw new Exception("O título deve ter no máximo 150 caracteres");
            }
        }

        if (isset($dados['descricao']) && strlen($dados['descricao']) < 10) {
            throw new Exception("A descrição deve ter no mínimo 10 caracteres");
        }
    }

    /**
     * Valida as datas do desafio
     *
     * @param string $dataInicio
     * @param string $dataFim
     * @throws Exception
     */
    private function validarDatas(string $dataInicio, string $dataFim): void
    {
        $inicio = strtotime($dataInicio);
        $fim = strtotime($dataFim);

        if (!$inicio || !$fim) {
            throw new Exception("Formato de data inválido. Use o formato YYYY-MM-DD");
        }

        if ($fim <= $inicio) {
            throw new Exception("A data de término deve ser posterior à data de início");
        }

        $hoje = strtotime(date('Y-m-d'));
        if ($inicio < $hoje) {
            throw new Exception("A data de início não pode ser anterior à data atual");
        }

        // Validar duração mínima (pelo menos 1 dia)
        $diferencaDias = ($fim - $inicio) / (60 * 60 * 24);
        if ($diferencaDias < 1) {
            throw new Exception("O desafio deve ter duração mínima de 1 dia");
        }
    }

    /**
     * Valida o nível de dificuldade
     *
     * @param string $nivel
     * @throws Exception
     */
    private function validarNivelDificuldade(string $nivel): void
    {
        $niveisValidos = ['Iniciante', 'Intermediario', 'Avancado', 'Extremo'];

        if (!in_array($nivel, $niveisValidos)) {
            throw new Exception("Nível de dificuldade inválido. Use: " . implode(', ', $niveisValidos));
        }
    }
}

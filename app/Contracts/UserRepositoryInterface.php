<?php

namespace App\Contracts;

interface UserRepositoryInterface
{
    /**
     * Retorna a instância do modelo Eloquent que o repositório gerencia.
     * Essencial para a injeção de relacionamentos dinâmicos entre os módulos.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel();

    /**
     * Busca um usuário pelo ID, permitindo o carregamento de relações dinâmicas.
     *
     * @param int $id
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findById(int $id, array $relations = []);

    /**
     * Busca um usuário pelo endereço de e-mail.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByEmail(string $email);

    /**
     * Cria um novo registro de usuário no sistema.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Remove um usuário do sistema pelo ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}

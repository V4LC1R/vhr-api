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
    public function findById(string $id, array $relations = []);

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
     * Atualiza um usuário existente.
     *
     * @param string $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(string $id, array $data);

    /**
     * Remove um usuário do sistema pelo ID.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;
}

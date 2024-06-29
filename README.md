<h1 align="center">
  UserAddressApi 👤
</h1>

<div align="center">
  <p>Simples RestAPI JWT para registro e autenticação de usuários onde cada usuário pode cadastrar vários endereços.</p>
  <p>Desenvolvida com <a href="https://laravel.com">Laravel 11</a> usando <a href="https://laravel.com/docs/11.x/sanctum">Laravel Sanctum</a> e <a href="https://www.docker.com/">Docker</a></p>
</div>

## :nazar_amulet: Instalação

### Requisitos

- Docker
- Docker Compose

### Passos de Instalação

1. Clone o repositório para a sua máquina local:

    ```bash
    git clone https://github.com/netosep/user-address-api.git && cd user-address-api
    ```

2. Copie o arquivo de ambiente e construa o container da aplicação utilizando o [Docker](https://www.docker.com/):

    ```bash
    cp .env.example .env && docker-compose up --build -d
    ```

3. Instale as dependencias, suba as migrations e gere a key da aplicação executando o comando:

    ```bash
    docker-compose exec php-fpm composer install-app
    ```

## :dizzy: Acesso

#### Após de executar os passos de instalação, a aplicação estará acessível em: [http://localhost:8080](http://localhost:8080)

## :book: Documentação da API

#### Toda a documentação via [Swagger](https://swagger.io/) está disponivel no endpoint `/api/documentation`

## :dart: Testes

Para rodar os testes, use o comando abaixo:

```bash
docker-compose exec php-fpm php artisan test
```

## :file_folder: Estrutura do Projeto
- `app/` - Contém os arquivos principais da aplicação.
- `database/` - Contém as migrações e seeders do banco de dados.
- `routes/` - Contém as definições de rotas da aplicação.
- `tests/` - Contém os testes automatizados.

#

<p align="center">
  <i>Developed with 🖤 by <a href="https://github.com/netosep">Neto Sepulveda</a></i>
</p>

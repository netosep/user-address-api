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

1. Copie o arquivo de ambiente e construa o container da aplicação utilizando o [Docker](https://www.docker.com/):

    ```bash
    cp .env.example .env && docker-compose up --build -d
    ```

2. Aguarde os containers subirem, execute as migrations e gere a key da aplicação com o seguinte comando:

    ```bash
    docker-compose exec php-fpm composer init-app
    ```

## :dizzy: Acesso

Depois de executar os passos de instalação, a aplicação estará acessível em: [http://localhost:8080](http://localhost:8080)

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
- `resources/` - Contém as views e outros recursos de frontend.
- `routes/` - Contém as definições de rotas da aplicação.
- `tests/` - Contém os testes automatizados.

#

<p align="center">
  <i>Developed with 🖤 by <a href="https://github.com/netosep">Neto Sepulveda</a></i>
</p>

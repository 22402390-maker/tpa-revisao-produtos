# Loja de Produtos – TPA 3ª etapa

Atividade de revisão de Técnicas de Programação Avançada (Laravel).

- **Repositório:** https://github.com/22402390-maker/tpa-revisao-produtos
- **Atividade original:** https://github.com/nato-re/tpa-revisao-produtos
- **Aluno:** Rafael Vieira

## O que foi feito

- R1 a R8: listagem, login/logout, middlewares `auth` e `vendedor`, cadastro validado, compra e minhas compras
- Bônus: controle de estoque

## Como rodar

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Usuários de teste (senha `senha123`): `ana@loja.test` e `bruno@loja.test` (vendedores), `carla@loja.test` e `diego@loja.test` (clientes).

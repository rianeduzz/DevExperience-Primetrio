# YOUTAN - Sistema de Gerenciamento de Eventos Culturais

Desenvolvido para o SENAI Dev Experience 2025 – Módulo A, o **YOUTAN** é uma aplicação web criada pela equipe **PrimeTrio** para auxiliar a empresa **Artes & Cultura S.A.** no gerenciamento completo de eventos culturais — desde o cadastro e divulgação até a inscrição de participantes e análise de feedback.

---

## Sumário
- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Requisitos do Sistema](#requisitos-do-sistema)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Instalação e Execução](#instalação-e-execução)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Equipe PrimeTrio](#equipe-primetrio)
- [Licença](#licença)

---

## Sobre o Projeto

O **YOUTAN** é um sistema web desenvolvido para facilitar o gerenciamento de eventos culturais.  
Ele conecta administradores, produtores e participantes de forma prática e segura, proporcionando uma experiência moderna, intuitiva e acessível para todos os perfis de usuários.

---

## Funcionalidades

### Requisitos Funcionais
| ID | Nome | Descrição |
|----|------|------------|
| RF01 | Cadastro de Eventos | Permite que produtores cadastrem eventos com nome, descrição, data, local, categoria e imagem. |
| RF02 | Visualização de Eventos | Todos os usuários podem visualizar a lista de eventos e aplicar filtros por categoria, data e local. |
| RF03 | Inscrição/Reserva | Participantes podem se inscrever em eventos e confirmar presença. |
| RF04 | Gerenciamento de Participantes | Produtores podem visualizar, confirmar e enviar comunicados aos inscritos. |
| RF05 | Autenticação de Usuários | Login e logout com diferentes níveis de permissão (administrador, produtor, participante). |
| RF06 | Edição de Perfil | Todos os usuários podem editar suas informações pessoais. |
| RF07 | Dashboard Administrativo | Administradores visualizam métricas gerais sobre eventos e usuários. |
| RF08 | Sistema de Notificações | Envio automático de e-mails com confirmações, lembretes e cancelamentos. |

### Requisitos Não Funcionais
| ID | Nome | Descrição |
|----|------|------------|
| RNF01 | Performance | Respostas rápidas, mesmo com alto volume de dados. |
| RNF02 | Segurança | Criptografia de dados e proteção contra ataques. |
| RNF03 | Acessibilidade | Interface acessível para todos os públicos. |
| RNF04 | Usabilidade | Navegação simples e intuitiva. |
| RNF05 | Manutenibilidade | Código limpo e modular. |
| RNF06 | Escalabilidade | Suporte ao aumento de eventos e usuários. |
| RNF07 | Confiabilidade | Sistema estável com backups regulares. |
| RNF08 | Interface Gráfica | Layout responsivo e moderno. |
| RNF09 | Temas | Opção de tema claro e escuro. |

---

## Requisitos do Sistema

- Servidor Web: Apache ou Nginx  
- Banco de Dados: MySQL (utilizar phpMyAdmin para gerenciamento)  
- Versão mínima do PHP: 8.0  
- Navegador: Chrome, Edge ou Firefox (versão mais recente)  

---

## Tecnologias Utilizadas

- HTML5 / CSS3 / JavaScript  
- PHP 8+  
- MySQL / phpMyAdmin  
- Bootstrap (design responsivo)  
- Font Awesome (ícones)  
- Git e GitHub (controle de versão)

---

## Instalação e Execução

### Pré-requisitos
- PHP 8 ou superior instalado  
- Servidor local (XAMPP, Laragon, WAMP etc.)  
- MySQL ativo  
- Git instalado  

### Passos para Execução
1. Clone o repositório:
   ```bash
   git clone https://github.com/rianeduzz/DevExperience-Primetrio.git

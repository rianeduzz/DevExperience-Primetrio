# Manual do Usuário - Youtan

Conteúdo:
- Visão geral do sistema
- Como cadastrar/manter ativos e manutenções
- Como consultar histórico e exportar CSV/PDF
- Módulo Previsão (IA): uso e geração de previsões
- Assistente IA: funcionamento e fallback (offline)
- Backup e Recuperação
- Suporte técnico

## Backup e Recuperação
- Backups automáticos: `scripts/backup_db.php` gera dump em `storage/backups`.
- Agende via Task Scheduler (Windows) ou cron (Linux) para execução periódica.
- Para restaurar use `scripts/restore_db.php caminho/backup.sql.gz`.

## Fallback da IA
- O assistente usa OpenAI quando chave válida; se indisponível usa base local (tabelas `chatbot_conhecimento` / `chatbot_respostas`).
- Caso não haja resposta local, contate suporte.

## Segurança
- Não compartilhe chaves da OpenAI. Use variável de ambiente `OPENAI_API_KEY` em produção.
- HTTPS obrigatório em produção.

## Contato & Suporte
- Email: suporte@empresa.com
- Logs: ver `storage/logs/chatbot.log` e logs do Apache/PHP em caso de falhas.

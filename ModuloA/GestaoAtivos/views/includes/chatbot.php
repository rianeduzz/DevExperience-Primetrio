<?php
// ...arquivo incluído em páginas (não inicia sessão aqui)...
?>
<div id="ya-chat-widget" style="position:fixed;bottom:20px;right:20px;z-index:2000;">
	<button id="ya-toggle" class="btn btn-primary" aria-haspopup="dialog" aria-controls="ya-container">Assistente IA</button>
	<div id="ya-container" role="dialog" aria-label="Assistente IA" aria-modal="false" style="display:none;width:360px;height:460px;background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.15);overflow:hidden;margin-top:10px;">
		<div style="background:#007bff;color:#fff;padding:8px;font-weight:600;display:flex;justify-content:space-between;align-items:center;">
			<span>Assistente IA</span>
			<button id="ya-close" class="btn btn-sm btn-light" aria-label="Fechar assistente">×</button>
		</div>
		<div id="ya-messages" tabindex="0" style="padding:10px;height:360px;overflow:auto;font-size:14px;background:#f8f9fb;"></div>
		<div style="padding:8px;border-top:1px solid #eee;display:flex;gap:8px;">
			<input id="ya-input" type="text" aria-label="Pergunta para assistente" placeholder="Digite sua pergunta..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;">
			<button id="ya-send" class="btn btn-primary">Enviar</button>
		</div>
	</div>
</div>

<script>
(function(){
	const toggle = document.getElementById('ya-toggle');
	const container = document.getElementById('ya-container');
	const closeBtn = document.getElementById('ya-close');
	const sendBtn = document.getElementById('ya-send');
	const input = document.getElementById('ya-input');
	const messages = document.getElementById('ya-messages');

	function addMessage(text, who='bot'){
		const d = document.createElement('div');
		d.style.marginBottom = '8px';
		if (who === 'user') {
			d.innerHTML = `<div style="text-align:right"><div style="display:inline-block;background:#007bff;color:#fff;padding:8px;border-radius:8px;">${escapeHtml(text)}</div></div>`;
		} else {
			d.innerHTML = `<div><div style="display:inline-block;background:#f1f1f1;color:#000;padding:8px;border-radius:8px;">${escapeHtml(text)}</div></div>`;
		}
		messages.appendChild(d);
		messages.scrollTop = messages.scrollHeight;
	}

	function escapeHtml(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

	toggle.addEventListener('click', ()=>{ 
		container.style.display = container.style.display === 'none' ? 'block' : 'none';
		if (container.style.display === 'block') {
			document.getElementById('ya-input').focus();
		}
	});
	closeBtn.addEventListener('click', ()=>{ container.style.display = 'none'; });
	sendBtn.addEventListener('click', enviar);
	input.addEventListener('keypress', e=>{ if(e.key==='Enter') enviar(); });

	const apiUrl = window.location.origin + '/DevExperience-Primetrio/ModuloA/GestaoAtivos/api/chatbot.php';
	let inProgress = false;

	async function enviar(){
		const q = input.value.trim();
		if(!q || inProgress) return;
		inProgress = true;
		addMessage(q, 'user');
		input.value = '';
		const loadingEl = document.createElement('div'); loadingEl.id='ya-loading'; loadingEl.textContent='Aguarde...'; loadingEl.style.margin='6px 0'; messages.appendChild(loadingEl); messages.scrollTop = messages.scrollHeight;

		try {
			const res = await fetch(apiUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ pergunta: q })
			});
			const data = await res.json();
			const el = document.getElementById('ya-loading'); if (el) el.remove();
			if (data.resposta) addMessage(data.resposta, 'bot');
			else if (data.error) addMessage('Erro: ' + data.error, 'bot');
			else addMessage('Sem resposta do serviço.', 'bot');
		} catch (err) {
			const el = document.getElementById('ya-loading'); if (el) el.remove();
			addMessage('Erro ao conectar com o serviço de IA.', 'bot');
			console.error(err);
		} finally { inProgress = false; }
	}
})();
</script>

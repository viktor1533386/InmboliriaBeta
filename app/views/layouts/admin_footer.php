    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>/js/app.js"></script>

<!-- SweetAlert Toast para Notificaciones (Flash) -->
<?php if (!empty($_SESSION['flash'])): ?>
  <?php 
    $flash = $_SESSION['flash']; 
    unset($_SESSION['flash']); 
  ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        background: '#FFFFFF', /* White card */
        color: '#0F172A',
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });

      Toast.fire({
        icon: '<?= $flash['type'] === 'success' ? 'success' : 'error' ?>',
        title: '<?= addslashes(htmlspecialchars($flash['message'])) ?>'
      });
    });
  </script>
<?php endif; ?>

<!-- Chat Interno Widget (Privado) -->
<?php if (!empty($_SESSION['usuario_id'])): ?>
<div id="chat-widget" style="position:fixed; bottom:20px; right:20px; z-index:9999;">
  <button id="chat-toggle" style="background:var(--primary, #0f172a); color:white; border:none; border-radius:50%; width:60px; height:60px; font-size:24px; box-shadow:0 4px 6px rgba(0,0,0,0.1); cursor:pointer; display:flex; align-items:center; justify-content:center; position:relative;">
    💬
  </button>
  <div id="chat-box" style="display:none; position:absolute; bottom:70px; right:0; width:350px; height:450px; background:white; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); flex-direction:column; overflow:hidden;">
    
    <!-- HEADER -->
    <div style="background:var(--primary, #0f172a); color:white; padding:15px; font-weight:bold; display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex; align-items:center; gap:10px;">
        <button id="chat-back" style="display:none; background:none; border:none; color:white; cursor:pointer; font-size:16px;">⬅</button>
        <span id="chat-title">Contactos</span>
      </div>
      <button id="chat-close" style="background:none; border:none; color:white; cursor:pointer; font-size:18px;">✖</button>
    </div>

    <!-- CONTACTS VIEW -->
    <div id="chat-contacts-view" style="flex:1; overflow-y:auto; background:#f9fafb; display:flex; flex-direction:column;">
      <!-- Loaded via JS -->
    </div>

    <!-- MESSAGES VIEW -->
    <div id="chat-messages-view" style="display:none; flex:1; flex-direction:column; overflow:hidden;">
      <div id="chat-messages" style="flex:1; padding:15px; overflow-y:auto; background:#f9fafb; display:flex; flex-direction:column; gap:10px;">
        <!-- Mensajes -->
      </div>
      <div style="padding:10px; border-top:1px solid #e5e7eb; background:white;">
        <form id="chat-form" style="display:flex; gap:10px;">
          <input type="text" id="chat-input" placeholder="Escribe un mensaje..." style="flex:1; padding:8px 12px; border:1px solid #d1d5db; border-radius:20px; outline:none;" required autocomplete="off">
          <button type="submit" style="background:var(--primary, #0f172a); color:white; border:none; border-radius:20px; padding:8px 15px; cursor:pointer; font-weight:bold;">Enviar</button>
        </form>
      </div>
    </div>
    
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const chatToggle = document.getElementById('chat-toggle');
  const chatBox = document.getElementById('chat-box');
  const chatClose = document.getElementById('chat-close');
  const chatBack = document.getElementById('chat-back');
  
  const chatTitle = document.getElementById('chat-title');
  const chatContactsView = document.getElementById('chat-contacts-view');
  const chatMessagesView = document.getElementById('chat-messages-view');
  
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const chatMessages = document.getElementById('chat-messages');
  
  let chatIsOpen = false;
  let activeContactId = null;
  let lastMessageCount = 0;

  chatToggle.addEventListener('click', () => {
    chatIsOpen = !chatIsOpen;
    chatBox.style.display = chatIsOpen ? 'flex' : 'none';
    if (chatIsOpen) {
      if (!activeContactId) {
        fetchContacts();
      } else {
        fetchMessages();
      }
    }
  });

  chatClose.addEventListener('click', () => {
    chatIsOpen = false;
    chatBox.style.display = 'none';
  });

  chatBack.addEventListener('click', () => {
    activeContactId = null;
    chatTitle.textContent = 'Contactos';
    chatBack.style.display = 'none';
    chatMessagesView.style.display = 'none';
    chatContactsView.style.display = 'flex';
    fetchContacts();
  });

  function formatTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
  }

  function fetchContacts() {
    fetch('<?= BASE_URL ?>/chat/contactos')
      .then(res => res.json())
      .then(data => {
        if (data.contactos) {
          chatContactsView.innerHTML = '';
          const rolMap = {
            admin: 'Admin', supervisor: 'Supervisor', vendedor: 'Vendedor', 
            scrum_master: 'Scrum Master', especialista_ti: 'TI', seguridad: 'Seguridad'
          };
          
          if(data.contactos.length === 0){
             chatContactsView.innerHTML = '<div style="padding:20px; text-align:center; color:#6b7280; font-size:0.9rem;">No hay otros usuarios activos.</div>';
          }

          data.contactos.forEach(c => {
            const btn = document.createElement('button');
            btn.style.width = '100%';
            btn.style.textAlign = 'left';
            btn.style.padding = '12px 15px';
            btn.style.background = 'white';
            btn.style.border = 'none';
            btn.style.borderBottom = '1px solid #f3f4f6';
            btn.style.cursor = 'pointer';
            btn.style.display = 'flex';
            btn.style.justifyContent = 'space-between';
            btn.style.alignItems = 'center';
            
            btn.onmouseover = () => btn.style.background = '#f9fafb';
            btn.onmouseout = () => btn.style.background = 'white';
            
            const rolStr = rolMap[c.rol] || c.rol;
            
            let html = `<div>
              <div style="font-weight:bold; color:#1f2937;">${c.nombre}</div>
              <div style="font-size:0.8rem; color:#6b7280;">${rolStr}</div>
            </div>`;
            
            if (c.mensajes_recibidos > 0) {
              html += `<div style="background:#ef4444; color:white; border-radius:50%; width:20px; height:20px; font-size:0.75rem; display:flex; align-items:center; justify-content:center; font-weight:bold;">${c.mensajes_recibidos}</div>`;
            }
            
            btn.innerHTML = html;
            
            btn.addEventListener('click', () => {
              activeContactId = c.id;
              chatTitle.textContent = c.nombre;
              chatBack.style.display = 'block';
              chatContactsView.style.display = 'none';
              chatMessagesView.style.display = 'flex';
              chatMessages.innerHTML = '<div style="text-align:center; padding:20px; color:#6b7280;">Cargando...</div>';
              lastMessageCount = 0;
              fetchMessages();
              chatInput.focus();
            });
            
            chatContactsView.appendChild(btn);
          });
        }
      })
      .catch(err => console.error("Error fetching contacts:", err));
  }

  function fetchMessages() {
    if (!activeContactId) return;
    fetch(`<?= BASE_URL ?>/chat/obtener?dest=${activeContactId}`)
      .then(res => res.json())
      .then(data => {
        if (data.mensajes) {
          if (data.mensajes.length === 0 && lastMessageCount === 0) {
             chatMessages.innerHTML = '<div style="text-align:center; padding:20px; color:#6b7280; font-size:0.9rem;">No hay mensajes previos.</div>';
          } else if (data.mensajes.length > 0) {
             if(chatMessages.innerHTML.includes('Cargando...') || chatMessages.innerHTML.includes('No hay mensajes')){
                chatMessages.innerHTML = '';
             }
          }
          
          const currentUserId = <?= (int)($_SESSION['usuario_id'] ?? 0) ?>;
          
          if (data.mensajes.length !== lastMessageCount) {
              chatMessages.innerHTML = '';
              data.mensajes.forEach(m => {
                const isMe = parseInt(m.remitente_id) === currentUserId;
                const bubble = document.createElement('div');
                bubble.style.maxWidth = '85%';
                bubble.style.padding = '8px 12px';
                bubble.style.borderRadius = '12px';
                bubble.style.fontSize = '0.9rem';
                bubble.style.lineHeight = '1.3';
                
                if (isMe) {
                  bubble.style.alignSelf = 'flex-end';
                  bubble.style.background = 'var(--primary, #0f172a)';
                  bubble.style.color = 'white';
                  bubble.style.borderBottomRightRadius = '2px';
                } else {
                  bubble.style.alignSelf = 'flex-start';
                  bubble.style.background = 'white';
                  bubble.style.color = '#1f2937';
                  bubble.style.border = '1px solid #e5e7eb';
                  bubble.style.borderBottomLeftRadius = '2px';
                }

                let html = `<div>${m.mensaje}</div>`;
                html += `<div style="font-size:0.7rem; text-align:right; margin-top:4px; opacity:0.7;">${formatTime(m.created_at)}</div>`;
                
                bubble.innerHTML = html;
                chatMessages.appendChild(bubble);
              });
              
              if (chatIsOpen) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
              }
              lastMessageCount = data.mensajes.length;
          }
        }
      })
      .catch(err => console.error("Error fetching messages:", err));
  }

  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!activeContactId) return;
    
    const msg = chatInput.value.trim();
    if (!msg) return;

    fetch('<?= BASE_URL ?>/chat/enviar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mensaje: msg, destinatario_id: activeContactId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        chatInput.value = '';
        fetchMessages();
      }
    })
    .catch(err => console.error("Error sending message:", err));
  });

  // Polling silencioso
  setInterval(() => {
    if (chatIsOpen) {
      if (activeContactId) {
        fetchMessages();
      } else {
        fetchContacts();
      }
    }
  }, 5000);
});
</script>
<?php endif; ?>

</body>
</html>

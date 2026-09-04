"use client";

import { useState } from 'react';

export default function AdminLoginPage() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const formData = new FormData();
      formData.append('username', username);
      formData.append('password', password);

      const res = await fetch('/sazon-cordoba/api/admin/login.php', {
        method: 'POST',
        body: formData,
        credentials: 'include' // Ensures the PHPSESSID cookie is set in the browser
      });

      const data = await res.json();
      
      if (data.ok) {
        // Redirect directly to the PHP admin panel!
        window.location.href = '/sazon-cordoba/api/admin/index.php';
      } else {
        setError(data.error || 'Usuario o contraseña incorrectos');
      }
    } catch (err) {
      setError('Error de conexión con el servidor.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#f4f6f8' }}>
      <div style={{ background: '#fff', padding: '48px 40px', borderRadius: '24px', boxShadow: '0 10px 40px rgba(0,0,0,0.05)', width: '100%', maxWidth: '420px', textAlign: 'center' }}>
        <img src="/sazon-cordoba/img/logos/logosason.jpg" alt="Sazón Córdoba" style={{ maxHeight: '60px', margin: '0 auto 24px', borderRadius: '8px' }} />
        <h1 style={{ fontSize: '1.5rem', marginBottom: '8px', color: '#1a1a1a', fontWeight: 'bold' }}>Panel de Administración</h1>
        <p style={{ color: '#5a6066', marginBottom: '32px', fontSize: '0.95rem' }}>Ingresa para gestionar el contenido del sitio</p>
        
        {error && (
          <div style={{ background: 'rgba(255,0,0,0.1)', border: '1px solid rgba(255,0,0,0.3)', color: '#ff6b6b', padding: '12px', borderRadius: '12px', marginBottom: '20px', fontSize: '0.9rem' }}>
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div style={{ marginBottom: '20px', textAlign: 'left' }}>
            <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: 600, marginBottom: '6px', color: '#5a6066' }}>Usuario o Correo</label>
            <input 
              type="text" 
              required 
              value={username}
              onChange={e => setUsername(e.target.value)}
              style={{ width: '100%', padding: '12px 16px', borderRadius: '12px', border: '1px solid #e9ecef', background: '#f8f9fa', fontSize: '1rem', outline: 'none' }} 
            />
          </div>
          <div style={{ marginBottom: '20px', textAlign: 'left' }}>
            <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: 600, marginBottom: '6px', color: '#5a6066' }}>Contraseña</label>
            <input 
              type="password" 
              required 
              value={password}
              onChange={e => setPassword(e.target.value)}
              style={{ width: '100%', padding: '12px 16px', borderRadius: '12px', border: '1px solid #e9ecef', background: '#f8f9fa', fontSize: '1rem', outline: 'none' }} 
            />
          </div>
          <button 
            type="submit" 
            disabled={loading}
            style={{ width: '100%', padding: '14px', borderRadius: '50px', border: 'none', background: 'linear-gradient(135deg, #ff6b00, #ffb703)', color: '#fff', fontSize: '1rem', fontWeight: 700, cursor: 'pointer', opacity: loading ? 0.7 : 1 }}
          >
            {loading ? 'Cargando...' : 'Ingresar'}
          </button>
        </form>
        <a href="/" style={{ display: 'block', marginTop: '20px', color: '#adb5bd', fontSize: '0.9rem', textDecoration: 'none' }}>
          ← Volver al sitio
        </a>
      </div>
    </div>
  );
}

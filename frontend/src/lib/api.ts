export const SITE_BASE_URL = process.env.NEXT_PUBLIC_SITE_URL || '/sazon-cordoba';

export function resolveSiteUrl(enlace?: string | null): string {
  if (!enlace) return '#';
  if (/^(https?:\/\/|#|mailto:|tel:)/.test(enlace)) return enlace;
  
  // Si el enlace apunta a la API, usa NEXT_PUBLIC_API_URL
  if (enlace.startsWith('api/')) {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || '/sazon-cordoba/api';
    // Removemos 'api/' del inicio ya que apiUrl ya contiene '/api'
    return `${apiUrl}/${enlace.substring(4)}`;
  }
  
  return `${SITE_BASE_URL}/${enlace.replace(/^\/+/, '')}`;
}

export async function fetchSiteData() {
  try {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1/sazon-cordoba/api';
    const res = await fetch(`${apiUrl}/site`, {
      next: { revalidate: 60 }
    });
    if (!res.ok) {
      const text = await res.text();
      console.error(`API Error: ${res.status} - ${text}`);
      throw new Error(`Failed to fetch site data: ${res.status}`);
    }
    return res.json();
  } catch (err) {
    console.error('Fetch exception:', err);
    throw err;
  }
}

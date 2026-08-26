export const SITE_BASE_URL = 'http://localhost/sasoncordoba';

export function resolveSiteUrl(enlace?: string | null): string {
  if (!enlace) return '#';
  if (/^(https?:\/\/|#|mailto:|tel:)/.test(enlace)) return enlace;
  return `${SITE_BASE_URL}/${enlace.replace(/^\/+/, '')}`;
}

export async function fetchSiteData() {
  const res = await fetch('http://localhost/sasoncordoba/api/site', {
    next: { revalidate: 60 } // ISR: Revalidate every 60 seconds
  });
  if (!res.ok) {
    throw new Error('Failed to fetch site data');
  }
  return res.json();
}

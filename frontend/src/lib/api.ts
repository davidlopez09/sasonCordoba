export async function fetchSiteData() {
  const res = await fetch('http://localhost/sazonCordoba/api/site', {
    next: { revalidate: 60 } // ISR: Revalidate every 60 seconds
  });
  if (!res.ok) {
    throw new Error('Failed to fetch site data');
  }
  return res.json();
}

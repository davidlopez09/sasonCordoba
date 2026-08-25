import type { Metadata } from "next";
import "./globals.scss";
import Script from "next/script";

export const metadata: Metadata = {
  title: "Sazón Córdoba | El Evento Gastronómico del Año",
  description: "Descubre Sazón Córdoba, el evento culinario más importante organizado por la Cámara de Comercio de Montería. Ven a degustar platillos increíbles.",
};

import AosInit from "@/components/AosInit";

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="es" className="scroll-smooth">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;700;800;900&display=swap" rel="stylesheet" />
      </head>
      <body>
        <AosInit />
        {children}
        <Script src="https://unpkg.com/@phosphor-icons/web" strategy="lazyOnload" />
      </body>
    </html>
  );
}

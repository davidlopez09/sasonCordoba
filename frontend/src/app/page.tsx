import { fetchSiteData } from "@/lib/api";
import Navbar from "@/components/Navbar";
import HeroSection from "@/components/HeroSection";
import IdentitySection from "@/components/IdentitySection";
import AboutSection from "@/components/AboutSection";
import ExponentesSection from "@/components/ExponentesSection";
import GaleriaSection from "@/components/GaleriaSection";
import ItinerarySection from "@/components/ItinerarySection";
import ParticipaSection from "@/components/ParticipaSection";
import DirectorioSection from "@/components/DirectorioSection";
import CountdownSection from "@/components/CountdownSection";
import FaqSection from "@/components/FaqSection";
import SponsorsSection from "@/components/SponsorsSection";
import ContactoSection from "@/components/ContactoSection";
import RegistroSection from "@/components/RegistroSection";
import FooterSection from "@/components/FooterSection";

export default async function Home() {
  const data = await fetchSiteData();
  const config = data.configuraciones || {};

  return (
    <main>
      <Navbar 
        navData={data.menu_nav} 
        config={config} 
        buttons={data.botones_nav} 
      />

      <HeroSection hero={data.hero} />

      {config.mostrar_about !== '0' && (
        <>
          <IdentitySection data={data.identidad} subtitle={data.subtitulos?.nosotros} />
          <AboutSection data={data.about} subtitle={data.subtitulos?.nosotros} config={config} />
        </>
      )}

      {config.mostrar_participa !== '0' && <ParticipaSection data={data.participa} subtitle={data.subtitulos?.participa} />}
      
      {config.mostrar_directorio !== '0' && <DirectorioSection data={data.directorio_expositores} subtitle={data.subtitulos?.directorio} />}
      
      {config.mostrar_countdown !== '0' && <CountdownSection config={config} />}

      {(config.mostrar_chefs !== '0' || config.mostrar_platillos !== '0') && (
        <ExponentesSection 
          exponentes={config.mostrar_chefs !== '0' ? data.exponentes : []} 
          platillos={config.mostrar_platillos !== '0' ? data.platillos_destacados : []} 
          subtitle={data.subtitulos?.chefs} 
          subtitleDishes={data.subtitulos?.platillos}
        />
      )}
      
      {config.mostrar_itinerario !== '0' && <ItinerarySection data={data.itinerario} subtitle={data.subtitulos?.itinerario} />}

      {config.mostrar_galeria !== '0' && <GaleriaSection data={data.galeria} subtitle={data.subtitulos?.galeria} config={config} />}
      
      {config.mostrar_registro !== '0' && <RegistroSection subtitle={data.subtitulos?.registro} />}
      
      {config.mostrar_faq !== '0' && <FaqSection data={data.faq} subtitle={data.subtitulos?.faq} />}
      
      {config.mostrar_sponsors !== '0' && <SponsorsSection data={data.patrocinadores} subtitle={data.subtitulos?.sponsors} />}
      
      {config.mostrar_contacto !== '0' && <ContactoSection subtitle={data.subtitulos?.contacto} config={config} />}

      <FooterSection data={data.footer} config={config} navData={data.menu_nav} />

    </main>
  );
}

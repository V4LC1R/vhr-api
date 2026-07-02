import { TooltipProvider } from "@/components/ui/tooltip";
import { Navbar } from "./sections/navbar";
import { Hero } from "./sections/hero";
import { Features } from "./sections/features";
import { Showcase } from "./sections/showcase";
import { RoadmapSection } from "./sections/roadmap-section";
import { Faq } from "./sections/faq";
import { Cta } from "./sections/cta";
import { Footer } from "./sections/footer";

export function Landing() {
    return (
        <TooltipProvider delay={150}>
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                <Navbar />
                <main className="flex-1">
                    <Hero />
                    <Features />
                    <Showcase />
                    <RoadmapSection />
                    <Faq />
                    <Cta />
                </main>
                <Footer />
            </div>
        </TooltipProvider>
    );
}

import type { LucideIcon } from "lucide-react";
import { Check, CircleDashed, CircleDot } from "lucide-react";

import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Progress, ProgressLabel, ProgressValue } from "@/components/ui/progress";
import { Section } from "../components/section";
import { roadmap, statusLabel, type RoadmapStatus } from "../data/roadmap";

interface StatusStyle {
    dot: string;
    icon: LucideIcon;
    itemIcon: LucideIcon;
    itemColor: string;
    badge: "default" | "secondary" | "outline";
}

const statusStyles: Record<RoadmapStatus, StatusStyle> = {
    done: {
        dot: "bg-primary text-primary-foreground",
        icon: Check,
        itemIcon: Check,
        itemColor: "text-primary",
        badge: "default",
    },
    "in-progress": {
        dot: "bg-background text-primary ring-2 ring-primary",
        icon: CircleDot,
        itemIcon: CircleDot,
        itemColor: "text-primary",
        badge: "secondary",
    },
    planned: {
        dot: "bg-muted text-muted-foreground",
        icon: CircleDashed,
        itemIcon: CircleDashed,
        itemColor: "text-muted-foreground",
        badge: "outline",
    },
};

export function RoadmapSection() {
    return (
        <Section
            id="roadmap"
            eyebrow="Road-map"
            title="Pra onde o VHR está indo"
            description="O que já está de pé, o que estamos construindo agora e o que vem pela frente."
        >
            <ol className="mx-auto max-w-3xl">
                {roadmap.map((phase, index) => {
                    const style = statusStyles[phase.status];
                    const DotIcon = style.icon;
                    const ItemIcon = style.itemIcon;
                    const isLast = index === roadmap.length - 1;

                    return (
                        <li key={phase.id} className="relative flex gap-5 pb-8 last:pb-0">
                            {!isLast && (
                                <span
                                    aria-hidden
                                    className="absolute top-11 bottom-0 left-5 w-px -translate-x-1/2 bg-border"
                                />
                            )}
                            <span
                                className={cn(
                                    "relative z-10 flex size-10 shrink-0 items-center justify-center rounded-full",
                                    style.dot,
                                )}
                            >
                                <DotIcon className="size-5" />
                            </span>

                            <Card className="flex-1">
                                <CardHeader>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge variant="outline">{phase.period}</Badge>
                                        <Badge variant={style.badge}>{statusLabel[phase.status]}</Badge>
                                    </div>
                                    <CardTitle className="mt-2 text-lg">{phase.title}</CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-4">
                                    <Progress value={phase.progress}>
                                        <ProgressLabel>Progresso</ProgressLabel>
                                        <ProgressValue />
                                    </Progress>
                                    <ul className="flex flex-col gap-2.5">
                                        {phase.items.map((item) => (
                                            <li
                                                key={item}
                                                className="flex items-start gap-2.5 text-sm text-muted-foreground"
                                            >
                                                <ItemIcon className={cn("mt-0.5 size-4 shrink-0", style.itemColor)} />
                                                <span>{item}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                            </Card>
                        </li>
                    );
                })}
            </ol>
        </Section>
    );
}

import { cn } from "@/lib/utils";
import { Icon } from "@iconify/react";
import { UnityInput } from "./unity-input";
import { useFormContext, useWatch } from "react-hook-form";

const weeklyHours = [
    {
        label:'20Hrs',
        value:20,
        moth:100
    },
    {
        label:'30Hrs',
        value:30,
        moth:100
    },
    {
        label:'36Hrs',
        value:36,
        moth:180
    },
    {
        label:'40Hrs',
        value:40,
        moth:200
    },
    {
        label:'44Hrs',
        value:44,
        moth:220
    }
]

type WeeklyHoursOption = typeof weeklyHours[0]

type Props = {
    name:string
}

export function WeeklyHours({name}:Props) {
    const { control, setValue } = useFormContext()
    const value = useWatch({ control, name })

    const handle = (option:WeeklyHoursOption)=>{
        setValue(name, option.value, { shouldValidate: true, shouldDirty: true })
    }

    return (
        <div className="grid gap-3 grid-cols-5">

            <div className="col-span-5">
                <UnityInput label="Horas Semanais" name={name}/>
            </div>

            <span className="col-span-5 text-center text-xs text-muted-foreground">
                ou selecione um valor comum
            </span>

            <div className="col-span-5 grid gap-2 grid-cols-5">
                {
                    weeklyHours.map((w,i)=>{
                        const isSelected = value === w.value

                        return (
                            <button
                                type="button"
                                key={i}
                                onClick={()=>handle(w)}
                                className={cn(
                                    "flex flex-col items-center justify-center gap-1 rounded-lg border py-2.5 px-1 cursor-pointer",
                                    "bg-muted border-[#8A8F85]/70 transition duration-300 hover:bg-warning/20",
                                    "dark:bg-input dark:hover:dark:bg-input/80",
                                    isSelected
                                        ? "border-accent bg-accent/10 dark:border-accent dark:bg-accent/10"
                                        : ""
                                )}
                            >
                                <span className="flex items-center gap-1">
                                    {isSelected && (
                                        <Icon icon="solar:check-circle-bold" className="size-3.5 text-primary dark:text-accent"/>
                                    )}
                                    <span className={cn("font-semibold text-sm",
                                        isSelected
                                            ? "text-primary dark:text-accent"
                                            : "text-[#8A8F85] dark:text-[#8C948B]"
                                    )}>
                                        {w.label}
                                    </span>
                                </span>
                            </button>
                        )
                    })
                }
            </div>
        </div>

    )
}

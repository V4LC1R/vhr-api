import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useCallback } from "react";

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
    value:number
    onChange:(value:WeeklyHoursOption)=>void
}

export function WeeklyHours({value,onChange}:Props) {

    const handle = (value:WeeklyHoursOption)=>{
        onChange(value)
    }

    return (
       
        <div className="grid gap-2 grid-cols-5">
            <h4 className="col-span-5 font-semibold">Horas Semanais</h4>
            {
                weeklyHours.map((w,i)=>{
                    const isSelected = value === w.value

                    return (
                        <div
                            key={i}
                            className={cn("bg-[#F4F1EA] cursor-pointer border border-[#8A8F85]/70 flex flex-col rounded-md p-2.5 items-center",
                                "transition duration-300 hover:bg-[#8A6D1F]/20",
                                "dark:bg-input dark:hover:dark:bg-input/80",
                                isSelected
                                    ? 'border-accent bg-accent/10 dark:border-accent dark:bg-accent/10'
                                    : ''
                            )}
                            onClick={()=>handle(w)}
                        >
                            <span className={cn("font-semibold",
                                isSelected
                                    ? 'text-primary dark:text-accent'
                                    : 'text-[#8A8F85] dark:text-[#8C948B]'
                            )}>
                                {w.label}
                            </span>
                        </div>
                    )
                })
            }
        </div>
       
    )
}
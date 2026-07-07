{{-- VHR — layout transacional de e-mail (table-based + estilos inline). --}}
{{-- Renderizado por App\Supports\Mail\EmailBuilder. Não referenciar CSS externo. --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>VHR</title>
</head>
<body style="margin:0;padding:0;background-color:#FAF9F5;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAF9F5;margin:0;padding:0;">
    <tr>
        <td align="center" style="padding:28px 16px;">

            @if ($preheader !== '')
                <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $preheader }}</div>
            @endif

            <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:560px;max-width:100%;border:1px solid #E6E1D6;border-radius:12px;overflow:hidden;background-color:#FFFFFF;">

                {{-- Header (esmeralda) --}}
                <tr>
                    <td align="center" style="background-color:#0C2A24;padding:30px 24px 26px;">
                        <div style="font-family:'Instrument Sans',Arial,Helvetica,sans-serif;font-size:30px;line-height:1;font-weight:700;letter-spacing:.14em;color:#F4F1EA;">VHR</div>
                        <div style="width:34px;height:2px;background-color:#CBB06B;margin:12px auto 10px;line-height:2px;font-size:0;">&nbsp;</div>
                        <div style="font-family:'Instrument Sans',Arial,Helvetica,sans-serif;font-size:10px;line-height:1;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:#CBB06B;">Valcir Human Resource</div>
                    </td>
                </tr>

                {{-- Corpo --}}
                <tr>
                    <td style="padding:32px 32px 28px;font-family:'Instrument Sans',Arial,Helvetica,sans-serif;">
                        @foreach ($blocks as $block)
                            @switch($block['type'])
                                @case('heading')
                                    <h1 style="margin:0 0 16px;font-size:21px;line-height:1.3;font-weight:600;color:#12281F;">{{ $block['text'] }}</h1>
                                    @break

                                @case('paragraph')
                                    <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#2C3A34;">{{ $block['text'] }}</p>
                                    @break

                                @case('button')
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0;">
                                        <tr>
                                            <td align="center" bgcolor="#CBB06B" style="border-radius:8px;">
                                                <a href="{{ $block['url'] }}" style="display:inline-block;padding:13px 30px;font-family:'Instrument Sans',Arial,Helvetica,sans-serif;font-size:15px;font-weight:600;line-height:1;color:#0C2A24;text-decoration:none;border-radius:8px;">{{ $block['label'] }}</a>
                                            </td>
                                        </tr>
                                    </table>
                                    @break

                                @case('muted')
                                    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#5F6B66;">{{ $block['text'] }}</p>
                                    @break

                                @case('divider')
                                    <div style="height:1px;background-color:#E6E1D6;line-height:1px;font-size:0;margin:6px 0 18px;">&nbsp;</div>
                                    @break

                                @case('urlText')
                                    <p style="margin:0;font-size:12px;line-height:1.6;color:#B08D3C;word-break:break-all;">{{ $block['url'] }}</p>
                                    @break
                            @endswitch
                        @endforeach
                    </td>
                </tr>

            </table>

            {{-- Footer --}}
            <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="width:560px;max-width:100%;">
                <tr>
                    <td align="center" style="padding:22px 24px 4px;font-family:'Instrument Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0 0 4px;font-size:12px;line-height:1.6;color:#8A938E;">Você recebeu este e-mail da VHR — Valcir Human Resource.</p>
                        <p style="margin:0;font-size:12px;line-height:1.6;color:#8A938E;">&copy; Valcir Human Resource &middot; Todos os direitos reservados</p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>

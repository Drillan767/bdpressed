@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://bedeprimee.josephlevarato.me/assets/images/logo.png" class="logo" alt="Logo Bédéprimée">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>

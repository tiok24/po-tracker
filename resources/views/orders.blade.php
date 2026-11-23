<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Production Order Tracker</title>
<style>
:root{--bg:#f4f7fc;--card:#fff;--tx:#14213d;--mu:#6b7a90;--bd:#e3e9f2;--ac:#2f6fe4;--track:#e6ebf3}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--tx);font:15px/1.5 Inter,system-ui,-apple-system,"Segoe UI",sans-serif}
header{background:linear-gradient(110deg,#15294d 0%,#1f4373 100%);color:#fff;padding:26px 0 24px}
.in{max-width:1000px;margin:0 auto;padding:0 20px}
.eyebrow{font-size:12px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#a9c4f5}
h1{margin:8px 0 4px;font-size:30px;line-height:1.2}
header p{margin:0;color:#dbe6f8}
main{padding-top:26px;padding-bottom:56px}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.stat{background:var(--card);border:1px solid var(--bd);border-radius:18px;padding:16px 20px}
.stat span{display:block;color:var(--mu);font-size:14px}
.stat b{display:block;font-size:32px;margin-top:4px;line-height:1.2}
form.bar{display:flex;gap:12px;margin-bottom:18px}
input,select,button{font:inherit;padding:11px 14px;border-radius:12px;border:1px solid var(--bd);background:var(--card);color:var(--tx)}
input{flex:1;min-width:0}
button{background:var(--ac);border-color:var(--ac);color:#fff;font-weight:600;cursor:pointer}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start}
.po{background:var(--card);border:1px solid var(--bd);border-radius:20px;padding:20px 22px}
.po h2{margin:0;font-size:18px;line-height:1.3}
.sub{color:var(--mu);font-size:14px;margin-top:2px}
.info{display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;margin-top:16px;font-size:14px;color:var(--mu)}
.info b{display:block;color:var(--tx);font-size:15px}
.prog{margin-top:16px}
.prog .row{display:flex;justify-content:space-between;font-size:14px;color:var(--mu)}
.prog .row b{color:var(--tx)}
.track{height:8px;background:var(--track);border-radius:99px;margin-top:6px;overflow:hidden}
.track i{display:block;height:100%;background:var(--ac);border-radius:99px}
.st{display:flex;align-items:center;gap:7px;margin-top:14px;padding-bottom:14px;border-bottom:1px solid var(--bd);font-weight:700;font-size:14px}
.st:before{content:"";width:8px;height:8px;border-radius:50%;background:currentColor}
.Waiting{color:#b7791f}.OnProcess{color:#2f6fe4}.Completed{color:#12805c}.Delayed{color:#c0392b}.Hold{color:#7a4fc4}
.note{margin-top:14px;font-size:14px;color:var(--mu)}
.empty{grid-column:1/-1;text-align:center;color:var(--mu);padding:40px;border:1px dashed var(--bd);border-radius:20px}
.pager{display:flex;justify-content:space-between;margin-top:20px;color:var(--mu);font-size:14px}
.pager a{color:var(--ac);text-decoration:none}
footer{text-align:center;color:#8c98aa;font-size:13px;padding-bottom:32px}
@media(max-width:760px){.stats{grid-template-columns:1fr 1fr}.grid{grid-template-columns:1fr}form.bar{flex-wrap:wrap}input{flex-basis:100%}h1{font-size:25px}}
</style>
</head>
<body>
<header><div class="in">
  <div class="eyebrow">Nama Perusahaan Anda</div>
  <h1>Production Order Tracker</h1>
  <p>Pantau progres pesanan produksi Anda dalam satu dashboard.</p>
</div></header>

<main class="in">
@php $c = fn ($k) => $counts[$k] ?? 0; @endphp
<div class="stats">
  <div class="stat"><span>Total pesanan</span><b>{{ $total }}</b></div>
  <div class="stat"><span>Dalam proses</span><b>{{ $c('On Process') }}</b></div>
  <div class="stat"><span>Selesai</span><b>{{ $c('Completed') }}</b></div>
  <div class="stat"><span>Perlu perhatian</span><b>{{ $c('Delayed') + $c('Hold') }}</b></div>
</div>

<form class="bar" method="get">
  <input name="q" value="{{ request('q') }}" placeholder="Cari nama customer, produk, atau nomor order…">
  <select name="status">
    <option value="">Semua status</option>
    @foreach($statuses as $s)
      <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->value }}</option>
    @endforeach
  </select>
  <button>Cari</button>
</form>

<div class="grid">
@forelse($orders as $po)
  @php $st = $po->display_status; @endphp
  <article class="po">
    <h2>{{ $po->product_name }}</h2>
    <div class="sub">{{ $po->order_number }} • {{ $po->customer_name }}</div>

    <div class="info">
      <div>Jumlah pesanan<b>{{ number_format($po->order_quantity, 0, ',', '.') }} pcs</b></div>
      <div>Estimated finish<b>{{ $po->estimated_finish?->format('Y-m-d') ?? '-' }}</b></div>
      <div>PO date<b>{{ $po->po_date?->format('Y-m-d') ?? '-' }}</b></div>
      <div>Production start<b>{{ $po->production_start?->format('Y-m-d') ?? '-' }}</b></div>
      @if($po->actual_finish)
        <div>Actual finish<b>{{ $po->actual_finish->format('Y-m-d') }}</b></div>
      @endif
    </div>

    <div class="prog">
      <div class="row"><span>Progres pengerjaan</span><b>{{ $po->progress }}%</b></div>
      <div class="track"><i style="width:{{ $po->progress }}%"></i></div>
    </div>

    <div class="st {{ str_replace(' ', '', $st->value) }}">{{ $st->value }}</div>

    @if($po->remarks)
      <div class="note">Remarks: {{ $po->remarks }}</div>
    @endif
  </article>
@empty
  <div class="empty">Tidak ada data pesanan.</div>
@endforelse
</div>

@if($orders->hasPages())
  <div class="pager">
    <span>@if($orders->previousPageUrl())<a href="{{ $orders->previousPageUrl() }}">← Sebelumnya</a>@endif</span>
    <span>Halaman {{ $orders->currentPage() }} dari {{ $orders->lastPage() }}</span>
    <span>@if($orders->nextPageUrl())<a href="{{ $orders->nextPageUrl() }}">Berikutnya →</a>@endif</span>
  </div>
@endif
</main>

<footer>Data diperbarui secara berkala oleh tim produksi.</footer>
</body>
</html>
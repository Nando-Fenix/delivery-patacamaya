@extends('layouts.negocio')
@section('titulo', 'Horarios — Delivery Patacamaya')
@section('contenido-negocio')
<div class="page-heading">
    <h1 class="h2">Horarios</h1>
    <p class="text-secondary">Configura un horario de apertura y cierre por día.</p>
</div>
<form class="card soft-card p-3 p-sm-4" method="POST" action="{{ route('negocio.horarios.update', $negocio) }}">
    @csrf @method('PUT')
    <div class="d-grid gap-3">
        @foreach($dias as $i => $dia)
            @php($horario = $existentes->get($dia))
            <div class="schedule-row">
                <input type="hidden" name="horarios[{{ $i }}][dia_semana]" value="{{ $dia }}">
                <strong class="text-capitalize">{{ str_replace(['miercoles', 'sabado'], ['miércoles', 'sábado'], $dia) }}</strong>
                <div><label class="small text-secondary" for="apertura-{{ $i }}">Apertura</label><input class="form-control" id="apertura-{{ $i }}" type="time" name="horarios[{{ $i }}][hora_apertura]" value="{{ old("horarios.$i.hora_apertura", $horario?->hora_apertura ? substr($horario->hora_apertura, 0, 5) : '08:00') }}"></div>
                <div><label class="small text-secondary" for="cierre-{{ $i }}">Cierre</label><input class="form-control" id="cierre-{{ $i }}" type="time" name="horarios[{{ $i }}][hora_cierre]" value="{{ old("horarios.$i.hora_cierre", $horario?->hora_cierre ? substr($horario->hora_cierre, 0, 5) : '20:00') }}"></div>
                <label class="form-check form-switch mb-0"><input type="hidden" name="horarios[{{ $i }}][cerrado]" value="0"><input class="form-check-input horario-cerrado" type="checkbox" name="horarios[{{ $i }}][cerrado]" value="1" @checked(old("horarios.$i.cerrado", $horario?->cerrado ?? false))><span>Cerrado</span></label>
            </div>
        @endforeach
    </div>
    @if($errors->any())<div class="alert alert-danger mt-3">Revisa que cada día abierto tenga horas válidas y que el cierre sea posterior.</div>@endif
    <button class="btn btn-primary touch-button mt-4 w-100">Guardar horarios</button>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.horario-cerrado').forEach((checkbox) => {
    const actualizar = () => checkbox.closest('.schedule-row').querySelectorAll('input[type=time]').forEach((input) => input.disabled = checkbox.checked);
    checkbox.addEventListener('change', actualizar);
    actualizar();
}));
</script>
@endsection

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { motifBuilder } from './motif-builder';

// Livewire dibundel manual supaya komponen Alpine buatan sendiri (Motif Builder)
// terdaftar sebelum Alpine dimulai. Lihat @livewireScriptConfig pada layout.
Alpine.data('motifBuilder', motifBuilder);

Livewire.start();

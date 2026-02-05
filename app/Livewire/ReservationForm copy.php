<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\ReservationUser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservationForm extends Component
{
    public ?Reservation $reservation = null;
    public ?int $reservation_id = null;

    public ?string $title = '';
    public ?string $staff = '';
    public ?string $description = null;
    public ?string $publish = null;
    public ?string $deadline = null;

    public bool $is_edit = false;


    public array $slots = [
        [
            'from' => null, 
            'to' => null, 
            'capacity' => null, 
            'reserved_users' => [], 
            'show_error_dialog' => false,
            '_delete' => false,
        ],
    ];

    public function mount(?Reservation $reservation = null): void
    {
        $this->reservation = $reservation;

        $this->reservation_id = $reservation->id;

        //新規作成か編集かを判別
        $this->is_edit = $reservation?->id ? true : false;


        if ($reservation) {

            $this->title       = $reservation->title;
            $this->staff       = $reservation->staff_name;
            $this->description = $reservation->description;
            $this->publish     = optional($reservation->publish_at)->format('Y-m-d\TH:i');
            $this->deadline    = optional($reservation->deadline_at)->format('Y-m-d\TH:i');

            // Slots（ReservationUser には触れない）
            $this->slots = $reservation->slots->map(function ($slot) {
                return [
                    'id'       => $slot->id, // 編集判定用
                    'from'     => optional($slot->start_at)->format('Y-m-d\TH:i'),
                    'to'       => optional($slot->end_at)->format('Y-m-d\TH:i'),
                    'capacity' => $slot->capacity,
                    'reserved_users' => $slot->users->map(function ($user) {
                                            return [
                                                'id'   => $user->id,
                                                'name' => $user->name,
                                                'email' => $user->email,
                                                '_delete' => false,
                                                '_create' => false,
                                            ];
                                        })->toArray(),
                    'show_error_dialog' => false,
                    '_delete' => false,
                ];
            })->toArray();
        }
        if (empty($this->slots)) {
            $this->slots = [
                [
                    'id' => null,
                    'from' => '', 
                    'to' => '', 
                    'capacity' => '', 
                    'reserved_users' => [], 
                    'show_error_dialog' => false,
                    '_delete' => false,
                ]
            ];
        }
    }

    public function addSlot(): void
    {
        $this->slots[] = [
            'id' => null,
            'from' => '', 
            'to' => '', 
            'capacity' => '', 
            'reserved_users' => [], 
            'show_error_dialog' => false,
            '_delete' => false,
        ];

    }

    public function removeSlot(int $index): void
    {
        // $this->slots[$index]['show_error_dialog'] = false;

        // if (! empty($this->slots[$index]['reserved_users'])) {
        //     $this->slots[$index]['show_error_dialog'] = true;
        //     return;
        // }

        // 既存スロットなら削除フラグ
        if (isset($this->slots[$index]['id'])) {
            $this->slots[$index]['_delete'] = true;
            return;
        }

        // 新規スロットなら即削除
        unset($this->slots[$index]);
        $this->slots = array_values($this->slots);
    }
    public function addUser(int $index, int $user_id): void
    {
        $user = User::find($user_id);
        $this->slots[$index]['reserved_users'][] = [
            'id'   => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            '_delete' => false,
            '_create' => true,
        ];
        // dd($this->slots );
    }

    public function removeUser(int $slot_index, int $user_index): void
    {

        // 既存ユーザーなら削除フラグ
        if (isset($this->slots[$slot_index]['reserved_users'][$user_index]['id'])) {
            $this->slots[$slot_index]['reserved_users'][$user_index]['_delete'] = true;
            return;
        }

        // 新規ユーザーなら即削除
        unset($this->slots[$slot_index]['reserved_users'][$user_index]);
        $this->slots[$slot_index]['reserved_users']
            = array_values($this->slots[$slot_index]['reserved_users']);
        
    }

    public function save()
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'staff' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'publish' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after:publish'],

            'slots' => ['required', 'array', 'min:1'],
            'slots.*.from' => ['required', 'date'],
            'slots.*.to' => ['required', 'date', 'after:slots.*.from'],
            'slots.*.capacity' => ['required', 'integer', 'min:1', $this->getCapacityValidator()],
        ]);
   
        $reservation = DB::transaction(function () {

            // Reservation 本体
            if ($this->is_edit) {
                $reservation = $this->reservation;
                $reservation->update([
                    'title'       => $this->title,
                    'staff_name'  => $this->staff,
                    'description' => $this->description,
                    'publish_at'  => Carbon::parse($this->publish),
                    'deadline_at' => Carbon::parse($this->deadline),
                ]);
            } else {
                $reservation = Reservation::create([
                    'created_by'  => Auth::id(),
                    'title'       => $this->title,
                    'staff_name'  => $this->staff,
                    'description' => $this->description,
                    'publish_at'  => Carbon::parse($this->publish),
                    'deadline_at' => Carbon::parse($this->deadline),
                ]);
            }

            // ===== スロット操作事前振り分け =====
            $deleteSlotIds = [];
            $updateSlots   = [];
            $createSlots   = [];

            foreach ($this->slots as $slot) {

                // Slot 削除
                if (!empty($slot['_delete']) && isset($slot['id'])) {
                    $deleteSlotIds[] = $slot['id'];
                    continue;
                }

                // Slot 更新
                if (isset($slot['id'])) {
                    $updateSlots[] = [
                        'id'       => $slot['id'],
                        'capacity' => $slot['capacity'],
                        'start_at' => Carbon::parse($slot['from']),
                        'end_at'   => Carbon::parse($slot['to']),
                    ];
                }

                // Slot 新規
                if (empty($slot['_delete']) && !isset($slot['id'])) {
                    $createSlots[] = [
                        'capacity'      => $slot['capacity'],
                        'current_count' => 0,
                        'start_at'      => Carbon::parse($slot['from']),
                        'end_at'        => Carbon::parse($slot['to']),
                    ];
                }
            }

            // ===== スロットテーブル操作 =====

            // Slot 削除
            if ($deleteSlotIds) {
                $reservation->slots()
                    ->whereIn('id', $deleteSlotIds)
                    ->delete();
            }

            // Slot 更新
            foreach ($updateSlots as $slot) {
                $reservation->slots()
                    ->where('id', $slot['id'])
                    ->update([
                        'capacity' => $slot['capacity'],
                        'start_at' => $slot['start_at'],
                        'end_at'   => $slot['end_at'],
                    ]);
            }

            // Slot 新規
            if ($createSlots) {

                // 戻り値を受け取る
                $createdSlots = $reservation->slots()->createMany($createSlots);

                // create対象だった slots の index を取得
                $createIndexes = collect($this->slots)
                    ->keys()
                    ->filter(fn ($i) => empty($this->slots[$i]['_delete']) && !isset($this->slots[$i]['id']))
                    ->values();

                // 作成順を前提に id を対応付け
                foreach ($createdSlots as $i => $slotModel) {
                    $this->slots[$createIndexes[$i]]['id'] = $slotModel->id;
                }
            }

            // ===== ユーザー予約操作事前振り分け =====
            
            $cancelUsers   = [];
            $reserveUsers  = [];

            foreach ($this->slots as $slot) {
                foreach ($slot['reserved_users'] as $user) {
                    // ユーザーキャンセル
                    if (!empty($user['_delete']) && isset($user['id'])) {
                        $cancelUsers[] = [
                            'slot_id' => $slot['id'],
                            'user_id' => $user['id'],
                        ];
                    }
                    // ユーザー予約
                    if (!empty($user['_create']) && isset($user['id'])) {
                        $reserveUsers[] = [
                            'slot_id' => $slot['id'],
                            'user_id' => $user['id'],
                        ];
                    }
                    
                }
            }

            // ===== スロットテーブル操作 =====
            // 一括キャンセル
            if ($cancelUsers) {

                //重複排除
                $cancelUsers = collect($cancelUsers)
                    ->unique(fn ($u) => $u['slot_id'].'-'.$u['user_id'])
                    ->values()
                    ->toArray();

                ReservationUser::where('status', 'reserved')
                    ->where(function ($q) use ($cancelUsers) {
                        foreach ($cancelUsers as $u) {
                            $q->orWhere(function ($q2) use ($u) {
                                $q2->where('slot_id', $u['slot_id'])
                                ->where('user_id', $u['user_id']);
                            });
                        }
                    })
                    ->update(['status' => 'canceled']);
            }
            // 一括予約
            if ($reserveUsers) {
                //重複排除
                $reserveUsers = collect($reserveUsers)
                    ->unique(fn ($u) => $u['slot_id'].'-'.$u['user_id'])
                    ->values()
                    ->toArray();

                foreach ($reserveUsers as $u) {
                    ReservationUser::updateOrCreate(
                        [
                            'slot_id' => $u['slot_id'],
                            'user_id' => $u['user_id'],
                        ],
                        [
                            'status' => 'reserved',
                        ]
                    );
                }
            }

            // 変更を受けた slot だけ再計算
            $affectedSlotIds = collect(array_merge($cancelUsers, $reserveUsers))
                ->pluck('slot_id')
                ->unique();

            ReservationSlot::whereIn('id', $affectedSlotIds)
                ->get()
                ->each
                ->recalcCurrentCount();

            return $reservation;
            
        });
        
        return redirect()->route('reservation.detail', ['reservation' => $reservation->id]);

    }
    protected function getCapacityValidator()
    {
        return function ($attribute, $value, $fail) {
            // 'slots.0.capacity' のような形式からインデックスを取得
            preg_match('/slots\.(\d+)\.capacity/', $attribute, $matches);
            $index = $matches[1];

            $slotData = $this->slots[$index] ?? null;

            if ($slotData && isset($slotData['reserved_users'])) {
                $reservedCount = count($slotData['reserved_users']);
                if ($value < $reservedCount) {
                    $fail("定員は既に予約中の {$reservedCount} 人以上にしてください。");
                }
            }
        };
    }
    public function render()
    {
        return view('livewire.reservation-form');
    }
}




<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\ReservationUser;
use App\Models\Purpose;
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
    public ?string $close = null;

    public bool $is_edit = false;

    public bool $show_user_modal = false;
    public int $active_slot_index = 0;

    public array $purposes = [];     // purpose 全件
    public ?string $selected_purpose = null; // 選択された purpose の key または id

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
        $this->is_edit = $reservation?->id ? true : false;

        // purpose 全取得
        $this->purposes = Purpose::all(['id', 'key', 'name'])->toArray();

        // 新規作成時の初期値
        if (!$this->is_edit) {
            $now = Carbon::now();

            $this->publish  = $now->format('Y-m-d\TH:i');
            $this->deadline = $now->copy()->addWeek()->format('Y-m-d\TH:i');
            $this->close    = $now->copy()->addWeeks(2)->format('Y-m-d\TH:i'); // 任意

            $this->slots = [[
                'id' => null,
                'from' => '',
                'to' => '',
                'capacity' => '',
                'reserved_users' => [],
                'show_error_dialog' => false,
                '_delete' => false,
            ]];

            return;
        }

        // 編集時に既存予約の purpose があるなら設定
        if ($reservation && $reservation->purpose_id) {
            $this->selected_purpose = $reservation->purpose_id;
        }

        if ($reservation) {
            $this->title        = $reservation->title;
            $this->staff        = $reservation->staff_name;
            $this->description  = $reservation->description;
            $this->publish      = optional($reservation->publish_at)->format('Y-m-d\TH:i');
            $this->deadline     = optional($reservation->deadline_at)->format('Y-m-d\TH:i');
            $this->close        = optional($reservation->close_at)->format('Y-m-d\TH:i');

            $this->slots = $reservation->slots->map(function ($slot) {
                return [
                    'id'       => $slot->id,
                    'from'     => optional($slot->start_at)->format('Y-m-d\TH:i'),
                    'to'       => optional($slot->end_at)->format('Y-m-d\TH:i'),
                    'capacity' => $slot->capacity,
                    'reserved_users' => $slot->users->map(function ($user) {
                        return [
                            'id'      => $user->id,
                            'name'    => $user->name,
                            'email'   => $user->email,
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
            $this->slots = [[
                'id' => null,
                'from' => '',
                'to' => '',
                'capacity' => '',
                'reserved_users' => [],
                'show_error_dialog' => false,
                '_delete' => false,
            ]];
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
        if (isset($this->slots[$index]['id'])) {
            $this->slots[$index]['_delete'] = true;
            return;
        }

        unset($this->slots[$index]);
        $this->slots = array_values($this->slots);
    }

    public function addUser(int $index, int $user_id): void
    {
        $user = User::find($user_id);

        $this->slots[$index]['reserved_users'][] = [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            '_delete' => false,
            '_create' => true,
        ];
    }

    public function removeUser(int $slot_index, int $user_index): void
    {
        if (isset($this->slots[$slot_index]['reserved_users'][$user_index]['id'])) {
            $this->slots[$slot_index]['reserved_users'][$user_index]['_delete'] = true;
            $this->slots[$slot_index]['reserved_users'][$user_index]['_create'] = false;
            return;
        }

        unset($this->slots[$slot_index]['reserved_users'][$user_index]);
        $this->slots[$slot_index]['reserved_users']
            = array_values($this->slots[$slot_index]['reserved_users']);
    }

    //予約テーブル保存処理
    public function save()
    {
        $this->validate(
            [
                'title'            => ['required', 'string', 'max:255'],
                'selected_purpose' => ['required', 'exists:purposes,id'], 
                'staff'            => ['required', 'string', 'max:255'],
                'description'      => ['nullable', 'string'],
                'publish'          => ['required', 'date'],
                'deadline'         => ['required', 'date', 'after:publish'],
                'close'            => ['required', 'date', 'after:deadline'],

                'slots'            => ['required', 'array', 'min:1'],
                'slots.*.from'     => ['required', 'date', 'after:deadline'],
                'slots.*.to'       => ['required', 'date', 'after:slots.*.from'],
                'slots.*.capacity' => ['required', 'integer', 'min:1', $this->getCapacityValidator()],
            ],
            [
                // 基本情報
                'title.required' => 'タイトルは必須です。',
                'title.max'      => 'タイトルは255文字以内で入力してください。',

                'selected_purpose.required' => '目的を選択してください。',
                'selected_purpose.exists'   => '選択された目的が不正です。',

                'staff.required' => '担当者名は必須です。',
                'staff.max'      => '担当者名は255文字以内で入力してください。',

                // 日時
                'publish.required' => '公開開始日時は必須です。',
                'publish.date'     => '公開開始日時の形式が正しくありません。',

                'deadline.required' => '締切日時は必須です。',
                'deadline.date'     => '締切日時の形式が正しくありません。',
                'deadline.after'    => '締切日時は公開開始日時より後の日時を指定してください。',

                'close.required' => '公開終了日時は必須です。',
                'close.date'     => '公開終了日時の形式が正しくありません。',
                'close.after'    => '公開終了日時は締切日時より後の日時を指定してください。',

                // スロット
                'slots.required' => '予約枠を1つ以上追加してください。',
                'slots.array'    => '予約枠の形式が正しくありません。',
                'slots.min'      => '予約枠を1つ以上追加してください。',

                'slots.*.from.required' => '予約開始日時は必須です。',
                'slots.*.from.date'     => '予約開始日時の形式が正しくありません。',
                'slots.*.from.after'    => '予約開始日時は締切日時より後の日時を指定してください。',

                'slots.*.to.required' => '予約終了日時は必須です。',
                'slots.*.to.date'     => '予約終了日時の形式が正しくありません。',
                'slots.*.to.after'    => '予約終了日時は予約開始日時より後の日時を指定してください。',

                'slots.*.capacity.required' => '定員は必須です。',
                'slots.*.capacity.integer'  => '定員は整数で入力してください。',
                'slots.*.capacity.min'      => '定員は1以上で入力してください。',
            ]
        );

        $reservation = DB::transaction(function () {
            // 編集内容保存
            if ($this->is_edit) {
                $reservation = $this->reservation;

                // すでに削除済の場合
                $updated = $reservation;
                if ($updated === 0) {
                    throw new \RuntimeException('already deleted');
                }

                $reservation
                // ->whereNull('deleted_at')
                ->update([
                    'title'       => $this->title,
                    'purpose_id'  => $this->selected_purpose, 
                    'staff_name'  => $this->staff,
                    'description' => $this->description,
                    'status'      => $reservation->is_published ? 'unpublished' : $reservation->status,
                    'publish_at'  => Carbon::parse($this->publish),
                    'deadline_at' => Carbon::parse($this->deadline),
                    'close_at'    => Carbon::parse($this->close),
                ]);
                
                // 変更履歴
                $reservation->histories()->create([
                    'editor_id' => Auth::id(),
                    'action'    => 'update',
                    'comment'   => '更新',
                ]);

            } else { 
                // 新規作成内容保存
                $reservation = Reservation::create([
                    'created_by'  => Auth::id(),
                    'title'       => $this->title,
                    'purpose_id'  => $this->selected_purpose, // ←ここで保存
                    'staff_name'  => $this->staff,
                    'description' => $this->description,
                    'publish_at'  => Carbon::parse($this->publish),
                    'deadline_at' => Carbon::parse($this->deadline),
                    'close_at'    => Carbon::parse($this->close),
                ]);

                // 新規作成履歴
                $reservation->histories()->create([
                    'editor_id' => Auth::id(),
                    'action'    => 'create',
                    'comment'   => '新規作成',
                ]);
            }

            $delete_slot_ids = [];
            $update_slots = [];
            $create_slots = [];

            foreach ($this->slots as $slot) {
                if (!empty($slot['_delete']) && isset($slot['id'])) {
                    $delete_slot_ids[] = $slot['id'];
                    continue;
                }

                if (isset($slot['id'])) {
                    $update_slots[] = [
                        'id'       => $slot['id'],
                        'capacity' => $slot['capacity'],
                        'start_at' => Carbon::parse($slot['from']),
                        'end_at'   => Carbon::parse($slot['to']),
                    ];
                }

                if (empty($slot['_delete']) && !isset($slot['id'])) {
                    $create_slots[] = [
                        'capacity'      => $slot['capacity'],
                        'current_count' => 0,
                        'start_at'      => Carbon::parse($slot['from']),
                        'end_at'        => Carbon::parse($slot['to']),
                    ];
                }
            }

            if ($delete_slot_ids) {
                $reservation->slots()
                    ->whereIn('id', $delete_slot_ids)
                    ->delete();
            }

            foreach ($update_slots as $slot) {
                $reservation->slots()
                    ->where('id', $slot['id'])
                    ->update([
                        'capacity' => $slot['capacity'],
                        'start_at' => $slot['start_at'],
                        'end_at'   => $slot['end_at'],
                    ]);
            }

            if ($create_slots) {
                $created_slots = $reservation->slots()->createMany($create_slots);

                $create_indexes = collect($this->slots)
                    ->keys()
                    ->filter(fn ($i) => empty($this->slots[$i]['_delete']) && !isset($this->slots[$i]['id']))
                    ->values();

                foreach ($created_slots as $i => $slot_model) {
                    $this->slots[$create_indexes[$i]]['id'] = $slot_model->id;
                }
            }

            $cancel_users = [];
            $reserve_users = [];

            foreach ($this->slots as $slot) {
                foreach ($slot['reserved_users'] as $user) {
                    if (!empty($user['_delete']) && isset($user['id'])) {
                        $cancel_users[] = [
                            'slot_id' => $slot['id'],
                            'user_id' => $user['id'],
                        ];
                    }

                    if (!empty($user['_create']) && isset($user['id'])) {
                        $reserve_users[] = [
                            'slot_id' => $slot['id'],
                            'user_id' => $user['id'],
                        ];
                    }
                }
            }

            if ($cancel_users) {
                $cancel_users = collect($cancel_users)
                    ->unique(fn ($u) => $u['slot_id'].'-'.$u['user_id'])
                    ->values()
                    ->toArray();

                ReservationUser::where('status', 'reserved')
                    ->where(function ($q) use ($cancel_users) {
                        foreach ($cancel_users as $u) {
                            $q->orWhere(function ($q2) use ($u) {
                                $q2->where('slot_id', $u['slot_id'])
                                   ->where('user_id', $u['user_id']);
                            });
                        }
                    })
                    ->update(['status' => 'canceled']);
            }

            if ($reserve_users) {
                $reserve_users = collect($reserve_users)
                    ->unique(fn ($u) => $u['slot_id'].'-'.$u['user_id'])
                    ->values()
                    ->toArray();

                foreach ($reserve_users as $u) {
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

            $affected_slot_ids = collect(array_merge($cancel_users, $reserve_users))
                ->pluck('slot_id')
                ->unique();

            ReservationSlot::whereIn('id', $affected_slot_ids)
                ->get()
                ->each
                ->recalcCurrentCount();


            //公開処理
            if ($reservation->publish_at && $reservation->deadline_at) {

                if ($reservation->publish_at->isFuture()) {
                    $reservation->update([
                        'status' => 'draft',
                    ]);

                } elseif ($reservation->deadline_at->isFuture()) {
                    $reservation->update([
                        'status' => 'published',
                    ]);
                } else {
                    $reservation->update([
                        'status' => 'expired',
                    ]);
                }
            }
     

            return $reservation;
        });

        return redirect()->route('reservation.detail', ['reservation' => $reservation->id]);
    }

    protected function getCapacityValidator()
    {
        return function ($attribute, $value, $fail) {
            preg_match('/slots\.(\d+)\.capacity/', $attribute, $matches);
            $index = $matches[1];

            $slot_data = $this->slots[$index] ?? null;

            if ($slot_data && isset($slot_data['reserved_users'])) {
                // $reserved_count = count($slot_data['reserved_users']);
                $reserved_count = collect($slot_data['reserved_users'])
                    ->filter(function ($user) {
                        return empty($user['_delete']);
                    })
                    ->count();

                if ($value < $reserved_count) {
                    $fail("定員は既に予約中の {$reserved_count} 人以上にしてください。");
                }
            }
        };
    }
    //モーダルを開く処理
    public function openUserModal(int $slot_index): void
    {
        $this->active_slot_index = $slot_index;
        $this->show_user_modal = true;
    }

    #[\Livewire\Attributes\On('userSelected')]
    public function onUserSelected(array $user): void
    {
        $this->slots[$this->active_slot_index]['reserved_users'][] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            '_delete' => false,
            '_create' => true,
        ];
    }

    #[\Livewire\Attributes\On('closeUserModal')]
    public function closeUserModal(): void
    {
        $this->show_user_modal = false;
    }

    public function render()
    {
        return view('livewire.reservation-form');
    }
}

<section>
    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">ชื่อ</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">อีเมล</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-2">
                    <p class="mb-0">
                        อีเมลของคุณยังไม่ได้รับการยืนยัน
                        <form method="post" action="{{ route('verification.send') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 align-baseline">
                                คลิกที่นี่เพื่อส่งอีเมลยืนยันอีกครั้ง
                            </button>
                        </form>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 mb-0 text-success">
                            ลิงก์ยืนยันใหม่ถูกส่งไปยังอีเมลของคุณแล้ว
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">บันทึก</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success">บันทึกเรียบร้อยแล้ว</span>
            @endif
        </div>
    </form>
</section>

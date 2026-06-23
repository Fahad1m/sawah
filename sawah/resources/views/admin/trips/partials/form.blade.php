@php use Illuminate\Support\Str; @endphp

<div class="form-group">
  <label>الوجهة</label>
  <input name="destination" value="{{ old('destination', optional($trip)->destination) }}" required>
</div>

<div class="form-group">
  <label>الوصف</label>
  <textarea name="description">{{ old('description', optional($trip)->description) }}</textarea>
</div>

<div class="form-group">
  <label>السعر</label>
  <input type="number" step="0.01" name="price" value="{{ old('price', optional($trip)->price) }}" required>
</div>

<div class="form-group">
  <label>المدة (أيام)</label>
  <input type="number" name="duration" value="{{ old('duration', optional($trip)->duration) }}" required>
</div>

<div class="form-group">
  <label>التقييم (0–5)</label>
  <input type="number" step="0.1" name="rating" value="{{ old('rating', optional($trip)->rating ?? 4.5) }}">
</div>

<div class="form-group">
  <label>المزايا (مفصولة بفواصل)</label>
  <input name="features" value="{{ old('features', $features ?? '') }}">
</div>

<div class="form-group">
  <label>الصورة</label>
  <input type="file" name="image" accept="image/*">
  @if(!empty($trip?->image))
    @php $img = Str::startsWith($trip->image,'http') ? $trip->image : asset('storage/'.$trip->image); @endphp
    <div style="margin-top:.5rem">
      <img src="{{ $img }}" style="height:80px;border-radius:8px;">
    </div>
  @endif
</div>

@error('destination')<div class="alert alert-error">{{ $message }}</div>@enderror
@error('price')      <div class="alert alert-error">{{ $message }}</div>@enderror
@error('duration')   <div class="alert alert-error">{{ $message }}</div>@enderror
@error('image')      <div class="alert alert-error">{{ $message }}</div>@enderror

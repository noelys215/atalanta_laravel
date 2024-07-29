<div x-data="{ confirmed: false }">
    <input type="checkbox" {{ $isAdmin ? 'checked' : '' }}
    @click="if (!confirmed) {
                     if (confirm('Are you sure you want to change the admin status?')) {
                       confirmed = true;
                       @this.toggle();
                     }
                     return false;
                   } else {
                     @this.toggle();
                   }">
</div>

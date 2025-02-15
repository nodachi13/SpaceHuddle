<template>
  <div class="module-count" v-if="session">
    <div class="module-count__item">
      <span class="module-count__count">{{ session.topicCount }}</span>
      <span class="module-name" v-if="session.topicCount === 1">
        {{ $t('moderator.molecule.moduleCount.topics') }}
      </span>
      <span class="module-name" v-else>
        {{ $t('moderator.molecule.moduleCountPlural.topics') }}
      </span>
    </div>
    <div class="module-count__item">
      <span class="module-count__count">{{ session.taskCount }}</span>
      <span class="module-name" v-if="session.taskCount === 1">
        {{ $t('moderator.molecule.moduleCount.tasks') }}
      </span>
      <span class="module-name" v-else>
        {{ $t('moderator.molecule.moduleCountPlural.tasks') }}
      </span>
    </div>
    <div class="module-count__item">
      <span class="module-count__count">{{ users.length }}</span>
      <span class="module-name" v-if="users.length === 1">
        {{ $t('moderator.molecule.moduleCount.users') }}
      </span>
      <span class="module-name" v-else>
        {{ $t('moderator.molecule.moduleCountPlural.users') }}
      </span>
    </div>
    <el-popover trigger="click" width="calc(var(--app-width) * 0.2)">
      <template #reference>
        <div class="module-count__item">
          <span class="module-count__count">{{ participants.length }}</span>
          <span class="module-name" v-if="participants.length === 1">
            {{ $t('moderator.molecule.moduleCount.participants') }}
          </span>
          <span class="module-name" v-else>
            {{ $t('moderator.molecule.moduleCountPlural.participants') }}
          </span>
        </div>
      </template>
      <el-space wrap class="participant-list">
        <el-badge
          v-for="participant in participants"
          :key="participant.id"
          :value="participant.ideaCount"
          type="primary"
        >
          <font-awesome-icon
            :icon="participant.avatar.symbol"
            :style="{ color: participant.avatar.color }"
          ></font-awesome-icon>
        </el-badge>
      </el-space>
    </el-popover>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import { Prop, Watch } from 'vue-property-decorator';
import { Session } from '@/types/api/Session';
import * as sessionService from '@/services/session-service';
import * as sessionRoleService from '@/services/session-role-service';
import { ParticipantInfo } from '@/types/api/Participant';
import { SessionRole } from '@/types/api/SessionRole';

@Options({
  components: {},
})
/* eslint-disable @typescript-eslint/no-explicit-any*/
export default class ModuleCount extends Vue {
  @Prop() session!: Session;
  participants: ParticipantInfo[] = [];
  users: SessionRole[] = [];
  readonly intervalTime = 3000;
  interval!: any;

  mounted(): void {
    this.startInterval();
  }

  startInterval(): void {
    this.interval = setInterval(this.onSessionChanged, this.intervalTime);
  }

  unmounted(): void {
    clearInterval(this.interval);
  }

  @Watch('session', { immediate: true })
  async onSessionChanged(): Promise<void> {
    await this.getParticipants();
    await this.getUsers();
  }

  async getParticipants(): Promise<void> {
    if (this.session) {
      sessionService.getParticipants(this.session.id).then((queryResult) => {
        this.participants = queryResult;
      });
    }
  }

  async getUsers(): Promise<void> {
    if (this.session) {
      sessionRoleService.getList(this.session.id).then((queryResult) => {
        this.users = queryResult;
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.module-count {
  display: flex;
  justify-content: space-between;
  flex-direction: column;
  align-items: flex-start;
  font-size: var(--font-size-small);
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-top: 1rem;
  line-height: 1;
  flex-wrap: wrap;
  gap: 0.6rem;

  &__item {
    white-space: nowrap;
  }

  &__count {
    background-color: var(--color-mint);
    color: white;
    padding: 0.1rem 0.7rem;
    letter-spacing: 0;
    font-size: var(--font-size-default);
    border-radius: 100px;
    margin-right: 0.2rem;
  }
}

.module-name {
  margin-left: 5px;
}

.participant-list::v-deep {
  font-size: 1.5rem;
  overflow-y: auto;
  scrollbar-color: var(--color-primary) var(--color-gray);
  scrollbar-width: thin;
  max-height: calc(var(--app-height) / 2);

  .el-space__item {
    padding-top: 0.5rem;
    padding-right: 0.5rem;
  }
}
</style>

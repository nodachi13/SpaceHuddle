import {
  apiExecuteDelete,
  apiExecuteGetHandled,
  apiExecutePost,
  apiExecutePut,
} from '@/services/api';
import EndpointType from '@/types/enum/EndpointType';
import EndpointAuthorisationType from '@/types/enum/EndpointAuthorisationType';
import { Task, TaskForSaveAction } from '@/types/api/Task';

export const getTaskById = async (
  taskId: string,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Task> => {
  return await apiExecuteGetHandled<Task>(
    `/${EndpointType.TASK}/${taskId}/`,
    {},
    authHeaderType
  );
};

export const updateTask = async (
  data: TaskForSaveAction
): Promise<TaskForSaveAction> => {
  return await apiExecutePut<TaskForSaveAction>(`/${EndpointType.TASK}/`, data);
};

export const deleteTask = async (id: string): Promise<void> => {
  return await apiExecuteDelete<any>(`/${EndpointType.TASK}/${id}/`);
};

export const getTaskList = async (
  topicId: string,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Task[]> => {
  return await apiExecuteGetHandled<Task[]>(
    `/${EndpointType.TOPIC}/${topicId}/${EndpointType.TASKS}/`,
    [],
    authHeaderType
  );
};

export const postTask = async (
  taskId: string,
  data: Partial<TaskForSaveAction>
): Promise<TaskForSaveAction> => {
  return await apiExecutePost<TaskForSaveAction>(
    `/${EndpointType.TOPIC}/${taskId}/${EndpointType.TASK}/`,
    data
  );
};

export const putTask = async (
  id: string,
  data: Partial<TaskForSaveAction>
): Promise<TaskForSaveAction> => {
  data['id'] = id;
  return await apiExecutePut<TaskForSaveAction>(
    `/${EndpointType.TASK}`,
    data,
    EndpointAuthorisationType.MODERATOR
  );
};
